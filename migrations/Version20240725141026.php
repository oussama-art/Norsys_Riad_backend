<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240725141026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room_image (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, image_name VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_8F81A5F454177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room_image ADD CONSTRAINT FK_8F81A5F454177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room DROP image_name, DROP updated_at, DROP image_size, DROP images');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_image DROP FOREIGN KEY FK_8F81A5F454177093');
        $this->addSql('DROP TABLE room_image');
        $this->addSql('ALTER TABLE room ADD image_name VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD image_size INT DEFAULT NULL, ADD images JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
