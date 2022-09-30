<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220828075852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE params (id INT AUTO_INCREMENT NOT NULL, last_preventive_date DATETIME DEFAULT NULL, last_stock_value_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock_value (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, date DATETIME NOT NULL, value INT NOT NULL, INDEX IDX_816744C59E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stock_value ADD CONSTRAINT FK_816744C59E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE part ADD qr_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE params');
        $this->addSql('DROP TABLE stock_value');
        $this->addSql('ALTER TABLE part DROP qr_code');
        $this->addSql('ALTER TABLE user DROP active');
    }
}
