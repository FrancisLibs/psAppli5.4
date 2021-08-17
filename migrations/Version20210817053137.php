<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210817053137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B7F6B75B26');
        $this->addSql('ALTER TABLE part DROP FOREIGN KEY FK_490F70C69E6B1585');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499E6B1585');
        $this->addSql('ALTER TABLE workshop DROP FOREIGN KEY FK_9B6F02C49E6B1585');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604CE34BEC');
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF841FDCE57C');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE work_order');
        $this->addSql('DROP TABLE workshop');
        $this->addSql('DROP INDEX IDX_8D93D6499E6B1585 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user DROP organisation_id, CHANGE first_name first_name VARCHAR(100) DEFAULT NULL, CHANGE last_name last_name VARCHAR(100) DEFAULT NULL, CHANGE phone_number phone_number VARCHAR(50) DEFAULT NULL, CHANGE email username VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, workshop_id INT NOT NULL, constructor VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, serial_number VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_1505DF841FDCE57C (workshop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE organisation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, street_number VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, zip_code VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, locality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone_number VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, designation VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, reference VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_490F70C69E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, part_id INT NOT NULL, place VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, qte_min INT DEFAULT NULL, qte_max INT DEFAULT NULL, qte_stock INT DEFAULT NULL, UNIQUE INDEX UNIQ_4B3656604CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE work_order (id INT AUTO_INCREMENT NOT NULL, machine_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, subject LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, duration DATE DEFAULT NULL, machine_stop VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:dateinterval)\', start_date DATE NOT NULL, end_date DATETIME DEFAULT NULL, INDEX IDX_DDD2E8B7A76ED395 (user_id), INDEX IDX_DDD2E8B7F6B75B26 (machine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workshop (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_9B6F02C49E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF841FDCE57C FOREIGN KEY (workshop_id) REFERENCES workshop (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C69E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B7F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C49E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('ALTER TABLE user ADD organisation_id INT NOT NULL, CHANGE first_name first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE last_name last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone_number phone_number VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE username email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D6499E6B1585 ON user (organisation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
