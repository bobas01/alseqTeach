<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Registre l\'acceptation des termes et de la politique de confidentialité.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings ADD legal_accepted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE bookings ADD legal_version VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings DROP legal_accepted_at');
        $this->addSql('ALTER TABLE bookings DROP legal_version');
    }
}
