<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone;

    #[ORM\Column(length: 11)]
    private string $cpf;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $frenchLevel = null;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $conversationGoals = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $intakeNote = null;

    public function __construct(string $email, string $name, ?string $phone, string $cpf)
    {
        $this->email = $email;
        $this->name = $name;
        $this->phone = $phone;
        $this->cpf = $cpf;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function sync(string $name, ?string $phone, string $cpf): void
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->cpf = $cpf;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    /**
     * @param list<string> $goals
     */
    public function updateIntake(?string $level, array $goals, ?string $note): void
    {
        if ($level !== null) {
            $this->frenchLevel = $level;
        }
        if ($goals !== []) {
            $this->conversationGoals = array_values($goals);
        }
        if ($note !== null) {
            $this->intakeNote = $note;
        }
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
}
