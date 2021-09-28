<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210927144608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, workshop_id INT NOT NULL, designation VARCHAR(255) NOT NULL, constructor VARCHAR(100) NOT NULL, model VARCHAR(100) NOT NULL, serial_number VARCHAR(100) DEFAULT NULL, status VARCHAR(10) NOT NULL, internal_code VARCHAR(8) NOT NULL, buy_date DATE DEFAULT NULL, INDEX IDX_1505DF841FDCE57C (workshop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisation (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, designation VARCHAR(100) NOT NULL, reference VARCHAR(100) DEFAULT NULL, validity TINYINT(1) NOT NULL, remarque LONGTEXT DEFAULT NULL, INDEX IDX_490F70C69E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, part_id INT NOT NULL, place VARCHAR(20) DEFAULT NULL, qte_min INT DEFAULT NULL, qte_max INT DEFAULT NULL, qte_stock INT DEFAULT NULL, UNIQUE INDEX UNIQ_4B3656604CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5F37A13BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, phone_number VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), INDEX IDX_8D93D6499E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workorder (id INT AUTO_INCREMENT NOT NULL, machine_id INT NOT NULL, user_id INT NOT NULL, organisation_id INT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(10) NOT NULL, start_date DATE DEFAULT NULL, start_time TIME NOT NULL, end_date DATE DEFAULT NULL, end_time TIME DEFAULT NULL, remark LONGTEXT DEFAULT NULL, request VARCHAR(255) DEFAULT NULL, implementation VARCHAR(255) DEFAULT NULL, type INT NOT NULL, duration_day INT DEFAULT NULL, duration_hour INT DEFAULT NULL, duration_minute INT DEFAULT NULL, stop_time_hour INT DEFAULT NULL, stop_time_minute INT DEFAULT NULL, INDEX IDX_51CF52BBF6B75B26 (machine_id), INDEX IDX_51CF52BBA76ED395 (user_id), INDEX IDX_51CF52BB9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workorder_part (id INT AUTO_INCREMENT NOT NULL, workorder_id INT NOT NULL, part_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_2B6A43992C1C3467 (workorder_id), INDEX IDX_2B6A43994CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workshop (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, name VARCHAR(100) NOT NULL, INDEX IDX_9B6F02C49E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF841FDCE57C FOREIGN KEY (workshop_id) REFERENCES workshop (id)');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C69E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE workorder ADD CONSTRAINT FK_51CF52BBF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE workorder ADD CONSTRAINT FK_51CF52BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE workorder ADD CONSTRAINT FK_51CF52BB9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE workorder_part ADD CONSTRAINT FK_2B6A43992C1C3467 FOREIGN KEY (workorder_id) REFERENCES workorder (id)');
        $this->addSql('ALTER TABLE workorder_part ADD CONSTRAINT FK_2B6A43994CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C49E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder DROP FOREIGN KEY FK_51CF52BBF6B75B26');
        $this->addSql('ALTER TABLE part DROP FOREIGN KEY FK_490F70C69E6B1585');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499E6B1585');
        $this->addSql('ALTER TABLE workorder DROP FOREIGN KEY FK_51CF52BB9E6B1585');
        $this->addSql('ALTER TABLE workshop DROP FOREIGN KEY FK_9B6F02C49E6B1585');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604CE34BEC');
        $this->addSql('ALTER TABLE workorder_part DROP FOREIGN KEY FK_2B6A43994CE34BEC');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
        $this->addSql('ALTER TABLE workorder DROP FOREIGN KEY FK_51CF52BBA76ED395');
        $this->addSql('ALTER TABLE workorder_part DROP FOREIGN KEY FK_2B6A43992C1C3467');
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF841FDCE57C');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE workorder');
        $this->addSql('DROP TABLE workorder_part');
        $this->addSql('DROP TABLE workshop');
    }
}
