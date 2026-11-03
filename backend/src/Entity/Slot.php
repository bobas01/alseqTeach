<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\SlotStatus;
use App\Repository\SlotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlotRepository::class)]
#[ORM\Table(name: 'slots')]
#[ORM\UniqueConstraint(name: 'uniq_slot_start', columns: ['starts_at'])]
class Slot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column]
    private int $priceCentavos;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column(enumType: SlotStatus::class, length: 20)]
    private SlotStatus $status = SlotStatus::Available;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $holdExpiresAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        int $priceCentavos,
        string $currency = 'BRL',
    ) {
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->priceCentavos = $priceCentavos;
        $this->currency = $currency;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function hold(\DateTimeImmutable $until): void
    {
        $this->status = SlotStatus::Held;
        $this->holdExpiresAt = $until;
    }

    public function release(): void
    {
        $this->status = SlotStatus::Available;
        $this->holdExpiresAt = null;
    }

    public function markBooked(): void
    {
        $this->status = SlotStatus::Booked;
        $this->holdExpiresAt = null;
    }

    public function block(): void
    {
        $this->status = SlotStatus::Blocked;
        $this->holdExpiresAt = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getPriceCentavos(): int
    {
        return $this->priceCentavos;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): SlotStatus
    {
        return $this->status;
    }

    public function getHoldExpiresAt(): ?\DateTimeImmutable
    {
        return $this->holdExpiresAt;
    }
}
