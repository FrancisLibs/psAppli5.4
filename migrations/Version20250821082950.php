<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821082950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_account_type (order_id INT NOT NULL, account_type_id INT NOT NULL, INDEX IDX_48D44E98D9F6D38 (order_id), INDEX IDX_48D44E9C6798DB (account_type_id), PRIMARY KEY(order_id, account_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_account_type ADD CONSTRAINT FK_48D44E98D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_account_type ADD CONSTRAINT FK_48D44E9C6798DB FOREIGN KEY (account_type_id) REFERENCES account_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_account_type DROP FOREIGN KEY FK_48D44E98D9F6D38');
        $this->addSql('ALTER TABLE order_account_type DROP FOREIGN KEY FK_48D44E9C6798DB');
        $this->addSql('DROP TABLE order_account_type');
    }
}
