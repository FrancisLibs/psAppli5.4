<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210823212212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, workshop_id INT NOT NULL, constructor VARCHAR(100) NOT NULL, model VARCHAR(100) NOT NULL, serial_number VARCHAR(100) DEFAULT NULL, status VARCHAR(10) NOT NULL, INDEX IDX_1505DF841FDCE57C (workshop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, designation VARCHAR(100) NOT NULL, reference VARCHAR(100) DEFAULT NULL, status VARCHAR(10) NOT NULL, code VARCHAR(10) NOT NULL, INDEX IDX_490F70C69E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, part_id INT NOT NULL, place VARCHAR(20) DEFAULT NULL, qte_min INT DEFAULT NULL, qte_max INT DEFAULT NULL, qte_stock INT DEFAULT NULL, UNIQUE INDEX UNIQ_4B3656604CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE work_order (id INT AUTO_INCREMENT NOT NULL, machine_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(10) NOT NULL, start_date DATE NOT NULL, end_date DATETIME DEFAULT NULL, subject LONGTEXT DEFAULT NULL, duration DATE DEFAULT NULL, machine_stop VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, INDEX IDX_DDD2E8B7F6B75B26 (machine_id), INDEX IDX_DDD2E8B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workshop (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, name VARCHAR(100) NOT NULL, INDEX IDX_9B6F02C49E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF841FDCE57C FOREIGN KEY (workshop_id) REFERENCES workshop (id)');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C69E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B7F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C49E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B7F6B75B26');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604CE34BEC');
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF841FDCE57C');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE work_order');
        $this->addSql('DROP TABLE workshop');
    }
}
