<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateBookingRequest;
use App\Entity\Booking;
use App\Entity\Slot;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Enum\SlotStatus;
use App\Exception\DomainException;
use App\Repository\BookingRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use App\Service\MercadoPago\MercadoPagoClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class BookingService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SlotRepository $slots,
        private BookingRepository $bookings,
        private UserRepository $users,
        private MercadoPagoClient $mercadoPago,
        private MeetingLinkGenerator $meetings,
        private LessonMailer $mailer,
        private LoggerInterface $logger,
        private int $bookingHoldMinutes,
        private string $mercadoPagoMode,
        private string $kernelEnvironment,
        private string $appTimezone,
    ) {
    }

    public function isSimulatedPix(): bool
    {
        return $this->mercadoPagoMode === 'stub';
    }

    public function releaseExpiredHolds(): int
    {
        return $this->em->wrapInTransaction(function (): int {
            return $this->releaseExpired(new \DateTimeImmutable());
        });
    }

    public function create(CreateBookingRequest $input): Booking
    {
        $booking = $this->em->wrapInTransaction(function () use ($input): Booking {
            return $this->reserve($input, new \DateTimeImmutable());
        });

        if ($booking->getStatus() === BookingStatus::AwaitingTeacher) {
            $this->mailer->sameDayRequested($booking);

            return $booking;
        }

        $this->attachPixOrFail($booking);
        $this->mailer->futureRequested($booking);

        return $booking;
    }

    public function teacherApprove(string $bookingId): Booking
    {
        $booking = $this->requireBooking($bookingId);
        if ($booking->getStatus() !== BookingStatus::AwaitingTeacher) {
            throw new DomainException('Este pedido já foi tratado.', 409, 'invalid_status');
        }

        $this->attachPixOrFail($booking);
        $this->mailer->teacherApproved($booking);

        return $booking;
    }

    public function teacherReject(string $bookingId): Booking
    {
        $booking = $this->requireBooking($bookingId);
        if ($booking->getStatus() !== BookingStatus::AwaitingTeacher) {
            throw new DomainException('Este pedido já foi tratado.', 409, 'invalid_status');
        }

        $this->em->wrapInTransaction(function () use ($booking): void {
            $this->cancelPending($booking);
        });
        $this->mailer->teacherRejected($booking);

        return $booking;
    }

    public function blockSlot(int $slotId): Slot
    {
        return $this->em->wrapInTransaction(function () use ($slotId): Slot {
            $this->releaseExpired(new \DateTimeImmutable());
            $slot = $this->requireSlot($slotId);
            if ($slot->getStatus() !== SlotStatus::Available) {
                throw new DomainException('Só é possível bloquear horários livres.', 409, 'slot_unavailable');
            }
            $slot->block();

            return $slot;
        });
    }

    public function unblockSlot(int $slotId): Slot
    {
        return $this->em->wrapInTransaction(function () use ($slotId): Slot {
            $slot = $this->requireSlot($slotId);
            if ($slot->getStatus() !== SlotStatus::Blocked) {
                throw new DomainException('Este horário não está bloqueado.', 409, 'invalid_status');
            }
            $slot->release();

            return $slot;
        });
    }

    /**
     * @return list<Booking>
     */
    public function listAwaitingTeacher(): array
    {
        $this->releaseExpiredHolds();

        return $this->bookings->findAwaitingTeacher();
    }

    /**
     * @return list<Booking>
     */
    public function listUpcoming(): array
    {
        $this->releaseExpiredHolds();

        return $this->bookings->findUpcoming(new \DateTimeImmutable());
    }

    /**
     * @param list<int> $slotIds
     *
     * @return array<int, Booking>
     */
    public function activeBookingsBySlotIds(array $slotIds): array
    {
        return $this->bookings->findActiveBySlotIds($slotIds);
    }

    public function sendDueReminders(?\DateTimeImmutable $now = null): int
    {
        $now ??= new \DateTimeImmutable();
        $timezone = new \DateTimeZone($this->appTimezone);
        $tomorrow = (new \DateTimeImmutable('tomorrow', $timezone))->setTime(0, 0);
        $dayAfter = $tomorrow->modify('+1 day');
        $bookings = $this->bookings->findPaidNeedingReminder(
            $tomorrow->setTimezone(new \DateTimeZone('UTC')),
            $dayAfter->setTimezone(new \DateTimeZone('UTC')),
        );

        foreach ($bookings as $booking) {
            $this->mailer->reminder($booking);
            $booking->markReminderSent($now);
        }
        if ($bookings !== []) {
            $this->em->flush();
        }

        return count($bookings);
    }

    /**
     * @param array<string, mixed> $payment
     */
    public function applyProviderPayment(array $payment): void
    {
        $reference = isset($payment['external_reference']) && is_scalar($payment['external_reference'])
            ? (string) $payment['external_reference']
            : '';
        $booking = $reference !== '' ? $this->bookings->find($reference) : null;
        if (!$booking instanceof Booking) {
            $this->logger->info('Paiement Mercado Pago sans réservation locale.', [
                'payment' => $payment['id'] ?? null,
            ]);

            return;
        }

        $status = isset($payment['status']) && is_scalar($payment['status']) ? (string) $payment['status'] : '';
        if ($status === 'approved') {
            if (!$this->paymentMatches($booking, $payment)) {
                $this->logger->error('Paiement Mercado Pago incohérent avec la réservation.', [
                    'booking' => $booking->getId(),
                    'payment' => $payment['id'] ?? null,
                ]);

                return;
            }
            $paymentId = isset($payment['id']) && is_scalar($payment['id']) ? (string) $payment['id'] : '';
            $updated = $this->em->wrapInTransaction(function () use ($booking, $paymentId): Booking {
                return $this->approve($booking, $paymentId);
            });
            if ($updated->getStatus() === BookingStatus::Paid) {
                $this->mailer->paid($updated);
            }

            return;
        }

        if (in_array($status, ['rejected', 'cancelled'], true)) {
            $this->em->wrapInTransaction(function () use ($booking): void {
                $this->cancelPending($booking);
            });
        }
    }

    public function confirmStub(string $bookingId): Booking
    {
        if ($this->mercadoPagoMode !== 'stub' || $this->kernelEnvironment === 'prod') {
            throw new DomainException('Não encontrado.', 404, 'not_found');
        }

        $booking = $this->requireBooking($bookingId);
        $updated = $this->em->wrapInTransaction(function () use ($booking): Booking {
            return $this->approve($booking, 'stub-'.$booking->getId());
        });
        if ($updated->getStatus() === BookingStatus::Paid) {
            $this->mailer->paid($updated);
        }

        return $updated;
    }

    private function reserve(CreateBookingRequest $input, \DateTimeImmutable $now): Booking
    {
        $this->releaseExpired($now);

        $slot = $this->slots->find($input->slotId);
        if ($slot === null) {
            throw new DomainException('Horário não encontrado.', 404, 'slot_not_found');
        }
        if ($slot->getStartsAt() <= $now || $slot->getStatus() !== SlotStatus::Available) {
            throw new DomainException('Este horário não está mais disponível.', 409, 'slot_unavailable');
        }

        $email = mb_strtolower(trim($input->studentEmail));
        $user = $this->users->findOneBy(['email' => $email]);
        if (!$user instanceof User) {
            $user = new User($email, trim($input->studentName), $input->studentPhone, $input->studentCpf);
            $this->em->persist($user);
        } else {
            $user->sync(trim($input->studentName), $input->studentPhone, $input->studentCpf);
        }

        $firstLesson = $user->getId() === null || !$this->bookings->hasPaidForUser($user);
        $level = LessonIntake::normalizeLevel($input->frenchLevel);
        $goals = LessonIntake::normalizeGoals($input->conversationGoals);
        $note = LessonIntake::normalizeNote($input->intakeNote);
        if ($firstLesson) {
            LessonIntake::assertForFirstLesson($level, $goals);
        } elseif ($level === null && $goals === [] && $note === null) {
            $level = $user->getFrenchLevel();
            $goals = $user->getConversationGoals();
            $note = $user->getIntakeNote();
        }
        $user->updateIntake($level, $goals, $note);

        $sameDay = $this->isSameLocalDay($slot->getStartsAt(), $now);
        $expiresAt = $sameDay ? $slot->getStartsAt() : $this->pixHoldUntil($now, $slot);
        $booking = new Booking($slot, $user, $this->meetings->createToken(), $expiresAt);
        $booking->attachIntake($firstLesson, $level, $goals, $note);
        $booking->acceptLegal(LegalTerms::VERSION, $now);
        if ($sameDay) {
            $booking->markAwaitingTeacher($expiresAt);
        }
        $slot->hold($expiresAt);
        $this->em->persist($booking);
        $this->em->flush();

        return $booking;
    }

    private function attachPixOrFail(Booking $booking): void
    {
        try {
            $payment = $this->mercadoPago->createPixPayment($booking);
        } catch (\Throwable $e) {
            $this->em->wrapInTransaction(function () use ($booking): void {
                $this->cancelPending($booking);
            });
            if ($e instanceof DomainException) {
                throw $e;
            }
            $this->logger->error('Échec inattendu pendant la création du Pix.', ['exception' => $e]);
            throw new DomainException('Não foi possível iniciar o Pix.', 502, 'payment_provider_error');
        }

        $now = new \DateTimeImmutable();
        $expiresAt = $this->pixHoldUntil($now, $booking->getSlot());
        $booking->attachPix($payment['id'], $payment['copyPaste'], $payment['qrCodeBase64'], $payment['ticketUrl']);
        $booking->markWaitingPix($expiresAt);
        $booking->getSlot()->hold($expiresAt);
        $this->em->flush();
    }

    private function pixHoldUntil(\DateTimeImmutable $now, Slot $slot): \DateTimeImmutable
    {
        $expiresAt = $now->modify(sprintf('+%d minutes', $this->bookingHoldMinutes));
        if ($expiresAt > $slot->getStartsAt()) {
            return $slot->getStartsAt();
        }

        return $expiresAt;
    }

    private function isSameLocalDay(\DateTimeImmutable $startsAt, \DateTimeImmutable $now): bool
    {
        $timezone = new \DateTimeZone($this->appTimezone);

        return $startsAt->setTimezone($timezone)->format('Y-m-d') === $now->setTimezone($timezone)->format('Y-m-d');
    }

    private function releaseExpired(\DateTimeImmutable $now): int
    {
        $slots = $this->slots->findExpiredHolds($now);
        foreach ($slots as $slot) {
            foreach ($this->bookings->findPendingForSlot($slot) as $booking) {
                $booking->expire();
            }
            $slot->release();
        }
        if ($slots !== []) {
            $this->em->flush();
        }

        return count($slots);
    }

    private function approve(Booking $booking, string $paymentId): Booking
    {
        $this->em->refresh($booking);
        $slot = $booking->getSlot();
        $this->em->refresh($slot);

        if ($booking->getStatus() === BookingStatus::Paid) {
            return $booking;
        }
        if (!in_array($booking->getStatus(), [BookingStatus::Pending, BookingStatus::Expired], true)) {
            return $booking;
        }
        if ($slot->getStatus() === SlotStatus::Booked || $this->heldBySomeoneElse($slot, $booking)) {
            $booking->cancel();

            return $booking;
        }

        $booking->markPaid($this->meetings->urlFor($booking->getMeetingToken()), new \DateTimeImmutable(), $paymentId);
        $slot->markBooked();

        return $booking;
    }

    private function cancelPending(Booking $booking): void
    {
        $this->em->refresh($booking);
        if (!in_array($booking->getStatus(), [BookingStatus::Pending, BookingStatus::AwaitingTeacher], true)) {
            return;
        }
        $slot = $booking->getSlot();
        $this->em->refresh($slot);
        $booking->cancel();
        if ($slot->getStatus() === SlotStatus::Held) {
            $slot->release();
        }
    }

    private function heldBySomeoneElse(Slot $slot, Booking $booking): bool
    {
        if ($slot->getStatus() !== SlotStatus::Held) {
            return false;
        }
        foreach ($this->bookings->findPendingForSlot($slot) as $pending) {
            if ($pending->getId() !== $booking->getId()) {
                return true;
            }
        }

        return false;
    }

    private function requireBooking(string $id): Booking
    {
        $booking = $this->bookings->find($id);
        if (!$booking instanceof Booking) {
            throw new DomainException('Reserva não encontrada.', 404, 'not_found');
        }

        return $booking;
    }

    private function requireSlot(int $id): Slot
    {
        $slot = $this->slots->find($id);
        if (!$slot instanceof Slot) {
            throw new DomainException('Horário não encontrado.', 404, 'slot_not_found');
        }

        return $slot;
    }

    /**
     * @param array<string, mixed> $payment
     */
    private function paymentMatches(Booking $booking, array $payment): bool
    {
        $currency = isset($payment['currency_id']) && is_scalar($payment['currency_id'])
            ? (string) $payment['currency_id']
            : 'BRL';
        $method = isset($payment['payment_method_id']) && is_scalar($payment['payment_method_id'])
            ? (string) $payment['payment_method_id']
            : '';
        if ($currency !== $booking->getCurrency() || $method !== 'pix' || !is_numeric($payment['transaction_amount'] ?? null)) {
            return false;
        }

        $paidCentavos = (int) round(((float) $payment['transaction_amount']) * 100);

        return $paidCentavos === $booking->getAmountCentavos();
    }
}
