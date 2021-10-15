<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211013181446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE machine_part (machine_id INT NOT NULL, part_id INT NOT NULL, INDEX IDX_1F059BBFF6B75B26 (machine_id), INDEX IDX_1F059BBF4CE34BEC (part_id), PRIMARY KEY(machine_id, part_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE machine_part ADD CONSTRAINT FK_1F059BBFF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE machine_part ADD CONSTRAINT FK_1F059BBF4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE machine_part');
    }
}
