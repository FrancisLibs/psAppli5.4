<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210913060847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder ADD start_time TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE workorder ADD CONSTRAINT FK_51CF52BB9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('CREATE INDEX IDX_51CF52BB9E6B1585 ON workorder (organisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder DROP FOREIGN KEY FK_51CF52BB9E6B1585');
        $this->addSql('DROP INDEX IDX_51CF52BB9E6B1585 ON workorder');
        $this->addSql('ALTER TABLE workorder DROP start_time');
    }
}
