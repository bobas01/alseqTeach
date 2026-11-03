<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021214800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le questionnaire de première aula.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings ADD french_level VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE bookings ADD conversation_goals CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE bookings ADD intake_note CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE bookings ADD first_lesson BOOLEAN DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE users ADD french_level VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD conversation_goals CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD intake_note CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookings DROP french_level');
        $this->addSql('ALTER TABLE bookings DROP conversation_goals');
        $this->addSql('ALTER TABLE bookings DROP intake_note');
        $this->addSql('ALTER TABLE bookings DROP first_lesson');
        $this->addSql('ALTER TABLE users DROP french_level');
        $this->addSql('ALTER TABLE users DROP conversation_goals');
        $this->addSql('ALTER TABLE users DROP intake_note');
    }
}
