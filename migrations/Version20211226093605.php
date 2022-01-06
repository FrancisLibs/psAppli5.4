<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226093605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delivery_note (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, organisation_id INT NOT NULL, number VARCHAR(100) NOT NULL, date DATE NOT NULL, INDEX IDX_1E21328EA53A8AA (provider_id), INDEX IDX_1E21328E9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_note_part (id INT AUTO_INCREMENT NOT NULL, delivery_note_id INT NOT NULL, part_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_AD102D0C2CF3B78B (delivery_note_id), INDEX IDX_AD102D0C4CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328EA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE delivery_note_part ADD CONSTRAINT FK_AD102D0C2CF3B78B FOREIGN KEY (delivery_note_id) REFERENCES delivery_note (id)');
        $this->addSql('ALTER TABLE delivery_note_part ADD CONSTRAINT FK_AD102D0C4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_note_part DROP FOREIGN KEY FK_AD102D0C2CF3B78B');
        $this->addSql('DROP TABLE delivery_note');
        $this->addSql('DROP TABLE delivery_note_part');
    }
}
