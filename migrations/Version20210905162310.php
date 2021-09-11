<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210905162310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder RENAME INDEX idx_ddd2e8b7f6b75b26 TO IDX_51CF52BBF6B75B26');
        $this->addSql('ALTER TABLE workorder RENAME INDEX idx_ddd2e8b7a76ed395 TO IDX_51CF52BBA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workorder RENAME INDEX idx_51cf52bba76ed395 TO IDX_DDD2E8B7A76ED395');
        $this->addSql('ALTER TABLE workorder RENAME INDEX idx_51cf52bbf6b75b26 TO IDX_DDD2E8B7F6B75B26');
    }
}
