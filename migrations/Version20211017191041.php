<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211017191041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder DROP FOREIGN KEY FK_51CF52BBF6B75B26');
        $this->addSql('DROP INDEX IDX_51CF52BBF6B75B26 ON workorder');
        $this->addSql('ALTER TABLE workorder DROP machine_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder ADD machine_id INT NOT NULL');
        $this->addSql('ALTER TABLE workorder ADD CONSTRAINT FK_51CF52BBF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_51CF52BBF6B75B26 ON workorder (machine_id)');
    }
}
