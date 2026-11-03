<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\DomainException;

final class LessonIntake
{
    /** @var array<string, string> */
    public const LEVELS = [
        'iniciante' => 'Nunca estudei / iniciante',
        'basico' => 'Básico',
        'intermediario' => 'Intermediário',
        'avancado' => 'Avançado / conversação',
    ];

    /** @var array<string, string> */
    public const GOALS = [
        'dia_a_dia' => 'Falar no dia a dia, com as pessoas',
        'colegas' => 'Conversar com colegas de trabalho',
        'assistentes' => 'Falar com assistentes ou a equipe',
        'administracao' => 'Falar com a administração',
        'entrevista' => 'Preparar uma entrevista em francês',
        'tecnico' => 'Linguagem técnica da minha área',
        'outro' => 'Outro objetivo',
    ];

    public static function normalizeLevel(?string $level): ?string
    {
        $level = $level !== null ? trim($level) : '';
        if ($level === '') {
            return null;
        }
        if (!isset(self::LEVELS[$level])) {
            throw new DomainException('Nível inválido.', 422, 'invalid_payload', [
                'frenchLevel' => 'Escolha um nível.',
            ]);
        }

        return $level;
    }

    /**
     * @param mixed $goals
     *
     * @return list<string>
     */
    public static function normalizeGoals(mixed $goals): array
    {
        if (!is_array($goals)) {
            return [];
        }
        $normalized = [];
        foreach ($goals as $goal) {
            if (!is_string($goal) || !isset(self::GOALS[$goal]) || in_array($goal, $normalized, true)) {
                continue;
            }
            $normalized[] = $goal;
        }

        return $normalized;
    }

    public static function normalizeNote(?string $note): ?string
    {
        $note = $note !== null ? trim($note) : '';
        if ($note === '') {
            return null;
        }
        if (mb_strlen($note) > 600) {
            throw new DomainException('O recado está longo demais.', 422, 'invalid_payload', [
                'intakeNote' => 'Máximo de 600 caracteres.',
            ]);
        }

        return $note;
    }

    /**
     * @param list<string> $goals
     */
    public static function assertForFirstLesson(?string $level, array $goals): void
    {
        $fields = [];
        if ($level === null) {
            $fields['frenchLevel'] = 'Informe seu nível.';
        }
        if ($goals === []) {
            $fields['conversationGoals'] = 'Escolha pelo menos um objetivo.';
        }
        if ($fields !== []) {
            throw new DomainException('Na primeira aula, conte seu nível e o que você quer trabalhar.', 422, 'invalid_payload', $fields);
        }
    }

    public static function levelLabel(?string $level): ?string
    {
        return $level !== null ? (self::LEVELS[$level] ?? $level) : null;
    }

    /**
     * @param list<string>|null $goals
     *
     * @return list<string>
     */
    public static function goalLabels(?array $goals): array
    {
        $labels = [];
        foreach ($goals ?? [] as $goal) {
            $labels[] = self::GOALS[$goal] ?? $goal;
        }

        return $labels;
    }
}
