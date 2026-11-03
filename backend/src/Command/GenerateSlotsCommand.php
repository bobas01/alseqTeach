<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Slot;
use App\Repository\SlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:slots:generate', description: 'Crée les créneaux ouverts (lun–ven, fuseau America/Sao_Paulo).')]
final class GenerateSlotsCommand extends Command
{
    /** @var list<int> */
    private const HOURS = [9, 10, 11, 14, 15, 16, 18, 19];

    public function __construct(
        private EntityManagerInterface $em,
        private SlotRepository $slots,
        private int $lessonPriceCentavos,
        private int $lessonDurationMinutes,
        private string $appTimezone,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('days', null, InputOption::VALUE_REQUIRED, 'Nombre de jours à couvrir, à partir d’aujourd’hui', '14');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int) $input->getOption('days');
        if ($days < 1 || $days > 60) {
            $output->writeln('<error>Le nombre de jours doit être entre 1 et 60.</error>');

            return Command::INVALID;
        }

        $timezone = new \DateTimeZone($this->appTimezone);
        $utc = new \DateTimeZone('UTC');
        $today = new \DateTimeImmutable('today', $timezone);
        $now = new \DateTimeImmutable('now', $timezone);
        $created = 0;

        for ($offset = 0; $offset < $days; $offset++) {
            $day = $today->modify(sprintf('+%d days', $offset));
            if ((int) $day->format('N') >= 6) {
                continue;
            }
            foreach (self::HOURS as $hour) {
                $localStart = $day->setTime($hour, 0);
                if ($localStart <= $now) {
                    continue;
                }
                $startsAt = $localStart->setTimezone($utc);
                if ($this->slots->existsAt($startsAt)) {
                    continue;
                }
                $endsAt = $localStart->modify(sprintf('+%d minutes', $this->lessonDurationMinutes))->setTimezone($utc);
                $this->em->persist(new Slot($startsAt, $endsAt, $this->lessonPriceCentavos));
                ++$created;
            }
        }

        $this->em->flush();
        $output->writeln(sprintf('%d créneau(x) créé(s).', $created));

        return Command::SUCCESS;
    }
}
