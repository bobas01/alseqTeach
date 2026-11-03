<?php

declare(strict_types=1);

namespace App\Dto;

use App\Service\Cpf;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback('validateCpf')]
final class CreateBookingRequest
{
    #[Assert\NotNull(message: 'Escolha um horário.')]
    #[Assert\Positive(message: 'Escolha um horário.')]
    public ?int $slotId = null;

    #[Assert\NotBlank(message: 'Informe seu nome.')]
    #[Assert\Length(min: 2, max: 120, minMessage: 'O nome está curto demais.', maxMessage: 'O nome está longo demais.')]
    public string $studentName = '';

    #[Assert\NotBlank(message: 'Informe seu e-mail.')]
    #[Assert\Email(message: 'E-mail inválido.')]
    #[Assert\Length(max: 180)]
    public string $studentEmail = '';

    #[Assert\Length(max: 30, maxMessage: 'Telefone longo demais.')]
    public ?string $studentPhone = null;

    #[Assert\NotBlank(message: 'Informe o CPF.')]
    public string $studentCpf = '';

    public ?string $frenchLevel = null;

    /** @var list<string> */
    public array $conversationGoals = [];

    public ?string $intakeNote = null;

    #[Assert\IsTrue(message: 'Aceite os Termos de Uso para continuar.')]
    public bool $acceptedTerms = false;

    #[Assert\IsTrue(message: 'Confirme que leu a Política de Privacidade.')]
    public bool $acceptedPrivacy = false;

    public function validateCpf(ExecutionContextInterface $context): void
    {
        if ($this->studentCpf !== '' && !Cpf::isValid($this->studentCpf)) {
            $context->buildViolation('CPF inválido.')->atPath('studentCpf')->addViolation();
        }
    }
}
