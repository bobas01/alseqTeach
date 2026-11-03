<?php

declare(strict_types=1);

namespace App\Http;

use App\Entity\Booking;
use App\Entity\Slot;
use App\Enum\BookingStatus;
use App\Service\CalendarInvite;
use App\Service\LessonIntake;
use App\Service\LessonMailer;

final class ApiPresenter
{
    public function __construct(
        private CalendarInvite $calendar,
        private LessonMailer $mailer,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function slot(Slot $slot): array
    {
        return [
            'id' => $slot->getId(),
            'startsAt' => $slot->getStartsAt()->format(\DateTimeInterface::ATOM),
            'endsAt' => $slot->getEndsAt()->format(\DateTimeInterface::ATOM),
            'priceCentavos' => $slot->getPriceCentavos(),
            'currency' => $slot->getCurrency(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function adminSlot(Slot $slot, ?Booking $booking = null): array
    {
        return [
            ...$this->slot($slot),
            'status' => $slot->getStatus()->value,
            'holdExpiresAt' => $slot->getHoldExpiresAt()?->format(\DateTimeInterface::ATOM),
            'booking' => $booking instanceof Booking ? $this->adminSlotBooking($booking) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function booking(Booking $booking, bool $simulatedPix): array
    {
        $pending = $booking->getStatus() === BookingStatus::Pending;
        $paid = $booking->getStatus() === BookingStatus::Paid;
        $lessonUrl = $this->mailer->lessonUrl($booking);

        return [
            'id' => $booking->getId(),
            'status' => $booking->getStatus()->value,
            'studentName' => $booking->getStudentName(),
            'amountCentavos' => $booking->getAmountCentavos(),
            'currency' => $booking->getCurrency(),
            'expiresAt' => $booking->getExpiresAt()?->format(\DateTimeInterface::ATOM),
            'paidAt' => $booking->getPaidAt()?->format(\DateTimeInterface::ATOM),
            'meetingUrl' => $paid ? $booking->getMeetingUrl() : null,
            'slot' => $this->slot($booking->getSlot()),
            'pix' => $pending ? [
                'copyPaste' => $booking->getPixCopyPaste(),
                'qrCodeBase64' => $booking->getPixQrCodeBase64(),
                'ticketUrl' => $booking->getPixTicketUrl(),
                'simulated' => $simulatedPix,
            ] : null,
            'calendar' => $paid ? [
                'icsUrl' => '/api/bookings/'.$booking->getId().'/calendar.ics',
                'googleUrl' => $this->calendar->googleUrl($booking, $lessonUrl),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function adminBooking(Booking $booking, bool $simulatedPix): array
    {
        $paid = $booking->getStatus() === BookingStatus::Paid;
        $lessonUrl = $this->mailer->lessonUrl($booking);
        $copy = $this->calendar->teacherCopy($booking, $lessonUrl);

        return [
            ...$this->booking($booking, $simulatedPix),
            'studentEmail' => $booking->getStudentEmail(),
            'studentPhone' => $booking->getStudent()->getPhone(),
            'createdAt' => $booking->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'teacherCalendar' => $paid ? [
                'icsUrl' => '/api/bookings/'.$booking->getId().'/calendar.ics',
                'googleUrl' => $this->calendar->googleUrl($booking, $lessonUrl, $copy['summary'], $copy['details']),
            ] : null,
            'intake' => $this->intake($booking),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adminSlotBooking(Booking $booking): array
    {
        $paid = $booking->getStatus() === BookingStatus::Paid;
        $lessonUrl = $this->mailer->lessonUrl($booking);
        $copy = $this->calendar->teacherCopy($booking, $lessonUrl);

        return [
            'id' => $booking->getId(),
            'status' => $booking->getStatus()->value,
            'studentName' => $booking->getStudentName(),
            'studentEmail' => $booking->getStudentEmail(),
            'studentPhone' => $booking->getStudent()->getPhone(),
            'amountCentavos' => $booking->getAmountCentavos(),
            'currency' => $booking->getCurrency(),
            'meetingUrl' => $paid ? $booking->getMeetingUrl() : null,
            'teacherCalendar' => $paid ? [
                'icsUrl' => '/api/bookings/'.$booking->getId().'/calendar.ics',
                'googleUrl' => $this->calendar->googleUrl($booking, $lessonUrl, $copy['summary'], $copy['details']),
            ] : null,
            'intake' => $this->intake($booking),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function intake(Booking $booking): ?array
    {
        if ($booking->getFrenchLevel() === null && $booking->getConversationGoals() === [] && $booking->getIntakeNote() === null && !$booking->isFirstLesson()) {
            return null;
        }

        return [
            'firstLesson' => $booking->isFirstLesson(),
            'level' => $booking->getFrenchLevel(),
            'levelLabel' => LessonIntake::levelLabel($booking->getFrenchLevel()),
            'goals' => $booking->getConversationGoals(),
            'goalLabels' => LessonIntake::goalLabels($booking->getConversationGoals()),
            'note' => $booking->getIntakeNote(),
        ];
    }
}
