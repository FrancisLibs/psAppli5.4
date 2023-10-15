<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231015061331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workshop DROP FOREIGN KEY FK_9B6F02C4727ACA70');
        $this->addSql('DROP INDEX IDX_9B6F02C4727ACA70 ON workshop');
        $this->addSql('ALTER TABLE workshop DROP parent_id, DROP level');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workshop ADD parent_id INT DEFAULT NULL, ADD level INT NOT NULL');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C4727ACA70 FOREIGN KEY (parent_id) REFERENCES workshop (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_9B6F02C4727ACA70 ON workshop (parent_id)');
    }
}
