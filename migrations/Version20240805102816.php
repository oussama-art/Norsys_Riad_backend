<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240805102816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, total_price NUMERIC(10, 2) NOT NULL, discount NUMERIC(10, 2) NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_reserved (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, room_id INT NOT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_ED442B06B83297E7 (reservation_id), INDEX IDX_ED442B0654177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room_reserved ADD CONSTRAINT FK_ED442B06B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE room_reserved ADD CONSTRAINT FK_ED442B0654177093 FOREIGN KEY (room_id) REFERENCES room (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_reserved DROP FOREIGN KEY FK_ED442B06B83297E7');
        $this->addSql('ALTER TABLE room_reserved DROP FOREIGN KEY FK_ED442B0654177093');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE room_reserved');
    }
}
 