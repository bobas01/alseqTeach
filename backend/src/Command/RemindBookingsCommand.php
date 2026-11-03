<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BookingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:bookings:remind', description: 'Envoie le rappel la veille des cours payés.')]
final class RemindBookingsCommand extends Command
{
    public function __construct(private BookingService $bookings)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->bookings->sendDueReminders();
        $output->writeln(sprintf('%d rappel(s) envoyé(s).', $count));

        return Command::SUCCESS;
    }
}
