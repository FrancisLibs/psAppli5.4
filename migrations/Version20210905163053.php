<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210905163053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workshop DROP FOREIGN KEY FK_9B6F02C49E6B1585');
        $this->addSql('DROP INDEX IDX_9B6F02C49E6B1585 ON workshop');
        $this->addSql('ALTER TABLE workshop CHANGE organisation_id oragnisation_id INT NOT NULL');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C4D1B36FB8 FOREIGN KEY (oragnisation_id) REFERENCES organisation (id)');
        $this->addSql('CREATE INDEX IDX_9B6F02C4D1B36FB8 ON workshop (oragnisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workshop DROP FOREIGN KEY FK_9B6F02C4D1B36FB8');
        $this->addSql('DROP INDEX IDX_9B6F02C4D1B36FB8 ON workshop');
        $this->addSql('ALTER TABLE workshop CHANGE oragnisation_id organisation_id INT NOT NULL');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C49E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_9B6F02C49E6B1585 ON workshop (organisation_id)');
    }
}
