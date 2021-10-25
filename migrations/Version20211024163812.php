<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211024163812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, workorder_id INT DEFAULT NULL, period INT NOT NULL, next_date DATE NOT NULL, UNIQUE INDEX UNIQ_5A3811FB2C1C3467 (workorder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB2C1C3467 FOREIGN KEY (workorder_id) REFERENCES workorder (id)');
        $this->addSql('ALTER TABLE stock ADD price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE workorder DROP cycle_days');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE schedule');
        $this->addSql('ALTER TABLE stock DROP price');
        $this->addSql('ALTER TABLE workorder ADD cycle_days INT DEFAULT NULL');
    }
}
