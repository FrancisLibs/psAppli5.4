<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250810214203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, number VARCHAR(10) NOT NULL, date DATETIME NOT NULL, designation VARCHAR(255) NOT NULL, INDEX IDX_F5299398A53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_part (order_id INT NOT NULL, part_id INT NOT NULL, INDEX IDX_4FE4AD18D9F6D38 (order_id), INDEX IDX_4FE4AD14CE34BEC (part_id), PRIMARY KEY(order_id, part_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_delivery_note (order_id INT NOT NULL, delivery_note_id INT NOT NULL, INDEX IDX_B3427A5C8D9F6D38 (order_id), INDEX IDX_B3427A5C2CF3B78B (delivery_note_id), PRIMARY KEY(order_id, delivery_note_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE order_part ADD CONSTRAINT FK_4FE4AD18D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_part ADD CONSTRAINT FK_4FE4AD14CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_delivery_note ADD CONSTRAINT FK_B3427A5C8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_delivery_note ADD CONSTRAINT FK_B3427A5C2CF3B78B FOREIGN KEY (delivery_note_id) REFERENCES delivery_note (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A53A8AA');
        $this->addSql('ALTER TABLE order_part DROP FOREIGN KEY FK_4FE4AD18D9F6D38');
        $this->addSql('ALTER TABLE order_part DROP FOREIGN KEY FK_4FE4AD14CE34BEC');
        $this->addSql('ALTER TABLE order_delivery_note DROP FOREIGN KEY FK_B3427A5C8D9F6D38');
        $this->addSql('ALTER TABLE order_delivery_note DROP FOREIGN KEY FK_B3427A5C2CF3B78B');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_part');
        $this->addSql('DROP TABLE order_delivery_note');
    }
}
