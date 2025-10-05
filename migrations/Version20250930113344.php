<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930113344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request_provider (request_id INT NOT NULL, provider_id INT NOT NULL, INDEX IDX_67E193B9427EB8A5 (request_id), INDEX IDX_67E193B9A53A8AA (provider_id), PRIMARY KEY(request_id, provider_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request_provider ADD CONSTRAINT FK_67E193B9427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request_provider ADD CONSTRAINT FK_67E193B9A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE provider DROP FOREIGN KEY FK_92C4739C418F94FA');
        $this->addSql('DROP INDEX IDX_92C4739C418F94FA ON provider');
        $this->addSql('ALTER TABLE provider DROP requests_id');
        $this->addSql('ALTER TABLE user DROP is_verified');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_provider DROP FOREIGN KEY FK_67E193B9427EB8A5');
        $this->addSql('ALTER TABLE request_provider DROP FOREIGN KEY FK_67E193B9A53A8AA');
        $this->addSql('DROP TABLE request_provider');
        $this->addSql('ALTER TABLE provider ADD requests_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE provider ADD CONSTRAINT FK_92C4739C418F94FA FOREIGN KEY (requests_id) REFERENCES request (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_92C4739C418F94FA ON provider (requests_id)');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL');
    }
}
