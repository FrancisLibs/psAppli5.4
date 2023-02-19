<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230213131326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE machine ADD parent_id INT DEFAULT NULL, ADD child_level INT DEFAULT NULL');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF84727ACA70 FOREIGN KEY (parent_id) REFERENCES machine (id)');
        $this->addSql('CREATE INDEX IDX_1505DF84727ACA70 ON machine (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF84727ACA70');
        $this->addSql('DROP INDEX IDX_1505DF84727ACA70 ON machine');
        $this->addSql('ALTER TABLE machine DROP parent_id, DROP child_level');
    }
}
