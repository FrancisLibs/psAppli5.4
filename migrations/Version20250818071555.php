<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818071555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_type (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(20) NOT NULL, letter VARCHAR(2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD account_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398C6798DB FOREIGN KEY (account_type_id) REFERENCES account_type (id)');
        $this->addSql('CREATE INDEX IDX_F5299398C6798DB ON `order` (account_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398C6798DB');
        $this->addSql('DROP TABLE account_type');
        $this->addSql('DROP INDEX IDX_F5299398C6798DB ON `order`');
        $this->addSql('ALTER TABLE `order` DROP account_type_id');
    }
}
