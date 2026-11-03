<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\BookingStatus;
use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'bookings')]
#[ORM\Index(name: 'idx_booking_status', columns: ['status'])]
class Booking
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Slot $slot;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private User $student;

    #[ORM\Column(length: 120)]
    private string $studentName;

    #[ORM\Column(length: 180)]
    private string $studentEmail;

    #[ORM\Column(length: 11)]
    private string $studentCpf;

    #[ORM\Column(enumType: BookingStatus::class, length: 20)]
    private BookingStatus $status = BookingStatus::Pending;

    #[ORM\Column]
    private int $amountCentavos;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $mercadoPagoPaymentId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $pixCopyPaste = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $pixQrCodeBase64 = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $pixTicketUrl = null;

    #[ORM\Column(length: 32)]
    private string $meetingToken;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $meetingUrl = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reminderSentAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $frenchLevel = null;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $conversationGoals = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $intakeNote = null;

    #[ORM\Column]
    private bool $firstLesson = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $legalAcceptedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $legalVersion = null;

    public function __construct(Slot $slot, User $student, string $meetingToken, \DateTimeImmutable $expiresAt)
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->slot = $slot;
        $this->student = $student;
        $this->studentName = $student->getName();
        $this->studentEmail = $student->getEmail();
        $this->studentCpf = $student->getCpf();
        $this->amountCentavos = $slot->getPriceCentavos();
        $this->currency = $slot->getCurrency();
        $this->meetingToken = $meetingToken;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function attachPix(string $paymentId, ?string $copyPaste, ?string $qrCodeBase64, ?string $ticketUrl): void
    {
        $this->mercadoPagoPaymentId = $paymentId;
        $this->pixCopyPaste = $copyPaste;
        $this->pixQrCodeBase64 = $qrCodeBase64;
        $this->pixTicketUrl = $ticketUrl;
    }

    public function markAwaitingTeacher(\DateTimeImmutable $expiresAt): void
    {
        $this->status = BookingStatus::AwaitingTeacher;
        $this->expiresAt = $expiresAt;
    }

    public function markWaitingPix(\DateTimeImmutable $expiresAt): void
    {
        $this->status = BookingStatus::Pending;
        $this->expiresAt = $expiresAt;
    }

    public function markPaid(string $meetingUrl, \DateTimeImmutable $paidAt, string $paymentId): void
    {
        $this->status = BookingStatus::Paid;
        $this->meetingUrl = $meetingUrl;
        $this->paidAt = $paidAt;
        $this->mercadoPagoPaymentId = $paymentId;
        $this->expiresAt = null;
    }

    public function markReminderSent(\DateTimeImmutable $at): void
    {
        $this->reminderSentAt = $at;
    }

    /**
     * @param list<string> $goals
     */
    public function attachIntake(bool $firstLesson, ?string $level, array $goals, ?string $note): void
    {
        $this->firstLesson = $firstLesson;
        $this->frenchLevel = $level;
        $this->conversationGoals = $goals === [] ? null : array_values($goals);
        $this->intakeNote = $note;
    }

    public function acceptLegal(string $version, \DateTimeImmutable $at): void
    {
        $this->legalVersion = $version;
        $this->legalAcceptedAt = $at;
    }

    public function expire(): void
    {
        if (in_array($this->status, [BookingStatus::Pending, BookingStatus::AwaitingTeacher], true)) {
            $this->status = BookingStatus::Expired;
        }
    }

    public function cancel(): void
    {
        if ($this->status !== BookingStatus::Paid) {
            $this->status = BookingStatus::Cancelled;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSlot(): Slot
    {
        return $this->slot;
    }

    public function getStudent(): User
    {
        return $this->student;
    }

    public function getStudentName(): string
    {
        return $this->studentName;
    }

    public function getStudentEmail(): string
    {
        return $this->studentEmail;
    }

    public function getStudentCpf(): string
    {
        return $this->studentCpf;
    }

    public function getStatus(): BookingStatus
    {
        return $this->status;
    }

    public function getAmountCentavos(): int
    {
        return $this->amountCentavos;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getMercadoPagoPaymentId(): ?string
    {
        return $this->mercadoPagoPaymentId;
    }

    public function getPixCopyPaste(): ?string
    {
        return $this->pixCopyPaste;
    }

    public function getPixQrCodeBase64(): ?string
    {
        return $this->pixQrCodeBase64;
    }

    public function getPixTicketUrl(): ?string
    {
        return $this->pixTicketUrl;
    }

    public function getMeetingToken(): string
    {
        return $this->meetingToken;
    }

    public function getMeetingUrl(): ?string
    {
        return $this->meetingUrl;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getReminderSentAt(): ?\DateTimeImmutable
    {
        return $this->reminderSentAt;
    }

    public function isFirstLesson(): bool
    {
        return $this->firstLesson;
    }

    public function getFrenchLevel(): ?string
    {
        return $this->frenchLevel;
    }

    /**
     * @return list<string>
     */
    public function getConversationGoals(): array
    {
        return $this->conversationGoals ?? [];
    }

    public function getIntakeNote(): ?string
    {
        return $this->intakeNote;
    }

    public function getLegalAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->legalAcceptedAt;
    }

    public function getLegalVersion(): ?string
    {
        return $this->legalVersion;
    }
}
