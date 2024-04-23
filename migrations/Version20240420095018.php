<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420095018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, request_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_3B978F9FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_part (request_id INT NOT NULL, part_id INT NOT NULL, INDEX IDX_36A8573C427EB8A5 (request_id), INDEX IDX_36A8573C4CE34BEC (part_id), PRIMARY KEY(request_id, part_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE request_part ADD CONSTRAINT FK_36A8573C427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request_part ADD CONSTRAINT FK_36A8573C4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX UNIQ_E6E132B48947610D ON organisation');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_490F70C677153098 ON part (code)');
        $this->addSql('ALTER TABLE provider ADD requests_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE provider ADD CONSTRAINT FK_92C4739C418F94FA FOREIGN KEY (requests_id) REFERENCES request (id)');
        $this->addSql('CREATE INDEX IDX_92C4739C418F94FA ON provider (requests_id)');
        $this->addSql('ALTER TABLE workshop DROP FOREIGN KEY FK_9B6F02C4727ACA70');
        $this->addSql('DROP INDEX IDX_9B6F02C4727ACA70 ON workshop');
        $this->addSql('ALTER TABLE workshop DROP parent_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE provider DROP FOREIGN KEY FK_92C4739C418F94FA');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9FA76ED395');
        $this->addSql('ALTER TABLE request_part DROP FOREIGN KEY FK_36A8573C427EB8A5');
        $this->addSql('ALTER TABLE request_part DROP FOREIGN KEY FK_36A8573C4CE34BEC');
        $this->addSql('DROP TABLE request');
        $this->addSql('DROP TABLE request_part');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E6E132B48947610D ON organisation (designation)');
        $this->addSql('DROP INDEX UNIQ_490F70C677153098 ON part');
        $this->addSql('DROP INDEX IDX_92C4739C418F94FA ON provider');
        $this->addSql('ALTER TABLE provider DROP requests_id');
        $this->addSql('ALTER TABLE workshop ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workshop ADD CONSTRAINT FK_9B6F02C4727ACA70 FOREIGN KEY (parent_id) REFERENCES workshop (id)');
        $this->addSql('CREATE INDEX IDX_9B6F02C4727ACA70 ON workshop (parent_id)');
    }
}
