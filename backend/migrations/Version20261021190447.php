<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261021190447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE bookings (
              id VARCHAR(36) NOT NULL,
              student_name VARCHAR(120) NOT NULL,
              student_email VARCHAR(180) NOT NULL,
              student_cpf VARCHAR(11) NOT NULL,
              status VARCHAR(20) NOT NULL,
              amount_centavos INTEGER NOT NULL,
              currency VARCHAR(3) NOT NULL,
              mercado_pago_payment_id VARCHAR(64) DEFAULT NULL,
              pix_copy_paste CLOB DEFAULT NULL,
              pix_qr_code_base64 CLOB DEFAULT NULL,
              pix_ticket_url VARCHAR(500) DEFAULT NULL,
              meeting_token VARCHAR(32) NOT NULL,
              meeting_url VARCHAR(512) DEFAULT NULL,
              created_at DATETIME NOT NULL,
              expires_at DATETIME DEFAULT NULL,
              paid_at DATETIME DEFAULT NULL,
              slot_id INTEGER NOT NULL,
              student_id INTEGER NOT NULL,
              PRIMARY KEY (id),
              CONSTRAINT FK_7A853C3559E5119C FOREIGN KEY (slot_id) REFERENCES slots (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_7A853C35CB944F1A FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX idx_booking_status ON bookings (status)');
        $this->addSql('CREATE INDEX IDX_7A853C3559E5119C ON bookings (slot_id)');
        $this->addSql('CREATE INDEX IDX_7A853C35CB944F1A ON bookings (student_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE slots (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              starts_at DATETIME NOT NULL,
              ends_at DATETIME NOT NULL,
              price_centavos INTEGER NOT NULL,
              currency VARCHAR(3) NOT NULL,
              status VARCHAR(20) NOT NULL,
              hold_expires_at DATETIME DEFAULT NULL,
              created_at DATETIME NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_slot_start ON slots (starts_at)');
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              email VARCHAR(180) NOT NULL,
              name VARCHAR(120) NOT NULL,
              phone VARCHAR(30) DEFAULT NULL,
              cpf VARCHAR(11) NOT NULL,
              created_at DATETIME NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bookings');
        $this->addSql('DROP TABLE slots');
        $this->addSql('DROP TABLE users');
    }
}
