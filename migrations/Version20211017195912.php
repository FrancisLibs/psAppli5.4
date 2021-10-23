<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211017195912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE workorder_machine (workorder_id INT NOT NULL, machine_id INT NOT NULL, INDEX IDX_BB56468C2C1C3467 (workorder_id), INDEX IDX_BB56468CF6B75B26 (machine_id), PRIMARY KEY(workorder_id, machine_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE workorder_machine ADD CONSTRAINT FK_BB56468C2C1C3467 FOREIGN KEY (workorder_id) REFERENCES workorder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workorder_machine ADD CONSTRAINT FK_BB56468CF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workorder ADD preventive TINYINT(1) NOT NULL, ADD template TINYINT(1) DEFAULT NULL, ADD cycle_days INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE workorder_machine');
        $this->addSql('ALTER TABLE workorder DROP preventive, DROP template, DROP cycle_days');
    }
}
