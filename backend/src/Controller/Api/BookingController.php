<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\CreateBookingRequest;
use App\Enum\BookingStatus;
use App\Exception\DomainException;
use App\Http\ApiPresenter;
use App\Repository\BookingRepository;
use App\Service\BookingService;
use App\Service\CalendarInvite;
use App\Service\Cpf;
use App\Service\LessonMailer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/bookings')]
final class BookingController
{
    public function __construct(
        private BookingService $bookings,
        private BookingRepository $repository,
        private ValidatorInterface $validator,
        private ApiPresenter $presenter,
        private CalendarInvite $calendar,
        private LessonMailer $mailer,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            throw new DomainException('JSON inválido.', 400, 'invalid_json');
        }

        $dto = new CreateBookingRequest();
        $dto->slotId = is_numeric($payload['slotId'] ?? null) ? (int) $payload['slotId'] : null;
        $dto->studentName = trim((string) ($payload['studentName'] ?? ''));
        $dto->studentEmail = trim((string) ($payload['studentEmail'] ?? ''));
        $phone = trim((string) ($payload['studentPhone'] ?? ''));
        $dto->studentPhone = $phone === '' ? null : $phone;
        $dto->studentCpf = Cpf::normalize((string) ($payload['studentCpf'] ?? ''));
        $dto->frenchLevel = isset($payload['frenchLevel']) && is_string($payload['frenchLevel'])
            ? trim($payload['frenchLevel'])
            : null;
        $dto->conversationGoals = is_array($payload['conversationGoals'] ?? null) ? $payload['conversationGoals'] : [];
        $note = isset($payload['intakeNote']) && is_string($payload['intakeNote']) ? trim($payload['intakeNote']) : '';
        $dto->intakeNote = $note === '' ? null : $note;
        $dto->acceptedTerms = ($payload['acceptedTerms'] ?? false) === true;
        $dto->acceptedPrivacy = ($payload['acceptedPrivacy'] ?? false) === true;

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $fields = [];
            foreach ($violations as $violation) {
                $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
            }
            throw new DomainException('Verifique os dados informados.', 422, 'invalid_payload', $fields);
        }

        $booking = $this->bookings->create($dto);

        return new JsonResponse($this->presenter->booking($booking, $this->bookings->isSimulatedPix()), 201);
    }

    #[Route('/{id}/calendar.ics', methods: ['GET'])]
    public function calendar(string $id): Response
    {
        $booking = $this->repository->find($id);
        if ($booking === null || $booking->getStatus() !== BookingStatus::Paid) {
            throw new DomainException('Reserva não encontrada.', 404, 'not_found');
        }

        $ics = $this->calendar->ics($booking, $this->mailer->lessonUrl($booking));

        return new Response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="aula-alseq-teach.ics"',
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $booking = $this->repository->find($id);
        if ($booking === null) {
            throw new DomainException('Reserva não encontrada.', 404, 'not_found');
        }

        return new JsonResponse($this->presenter->booking($booking, $this->bookings->isSimulatedPix()));
    }
}
