<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021201500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute reminder_sent_at aux réservations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings ADD reminder_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings DROP reminder_sent_at');
    }
}
