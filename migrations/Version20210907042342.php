<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210907042342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder ADD remark LONGTEXT DEFAULT NULL, ADD type VARCHAR(20) NOT NULL, CHANGE subject subject VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE workshop RENAME INDEX idx_9b6f02c4d1b36fb8 TO IDX_9B6F02C49E6B1585');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder DROP remark, DROP type, CHANGE subject subject LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE workshop RENAME INDEX idx_9b6f02c49e6b1585 TO IDX_9B6F02C4D1B36FB8');
    }
}
