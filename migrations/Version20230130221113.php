<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230130221113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX unique_machine ON machine');
        $this->addSql('ALTER TABLE machine CHANGE status status TINYINT(1) NOT NULL, CHANGE image_name image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX Unique_Code ON part');
        $this->addSql('ALTER TABLE part CHANGE qr_code qr_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE user RENAME INDEX service_id TO IDX_8D93D649ED5CA9E6');
        $this->addSql('ALTER TABLE workorder_part ADD price DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328EA76ED395');
        $this->addSql('ALTER TABLE machine CHANGE status status VARCHAR(10) NOT NULL, CHANGE image_name image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX unique_machine ON machine (internal_code)');
        $this->addSql('ALTER TABLE part CHANGE qr_code qr_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX Unique_Code ON part (code)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649ED5CA9E6');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_8d93d649ed5ca9e6 TO service_id');
        $this->addSql('ALTER TABLE workorder_part DROP price');
    }
}
