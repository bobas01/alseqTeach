<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BookingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:holds:release', description: 'Libère les créneaux dont la retenue Pix a expiré.')]
final class ReleaseExpiredHoldsCommand extends Command
{
    public function __construct(private BookingService $bookings)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->bookings->releaseExpiredHolds();
        $output->writeln(sprintf('%d créneau(x) libéré(s).', $count));

        return Command::SUCCESS;
    }
}
