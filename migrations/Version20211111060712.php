<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211111060712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE workorder_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE workorder ADD workorder_status_id INT DEFAULT NULL, ADD template_number INT DEFAULT NULL, DROP status');
        $this->addSql('ALTER TABLE workorder ADD CONSTRAINT FK_51CF52BB2AFD0F06 FOREIGN KEY (workorder_status_id) REFERENCES workorder_status (id)');
        $this->addSql('CREATE INDEX IDX_51CF52BB2AFD0F06 ON workorder (workorder_status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder DROP FOREIGN KEY FK_51CF52BB2AFD0F06');
        $this->addSql('DROP TABLE workorder_status');
        $this->addSql('DROP INDEX IDX_51CF52BB2AFD0F06 ON workorder');
        $this->addSql('ALTER TABLE workorder ADD status VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP workorder_status_id, DROP template_number');
    }
}
