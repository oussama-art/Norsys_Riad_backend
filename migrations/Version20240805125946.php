<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240805125946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_reserved DROP FOREIGN KEY FK_ED442B0654177093');
        $this->addSql('ALTER TABLE room_reserved DROP FOREIGN KEY FK_ED442B06B83297E7');
        $this->addSql('DROP TABLE room_reserved');
        $this->addSql('ALTER TABLE reservation ADD room_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('CREATE INDEX IDX_42C8495554177093 ON reservation (room_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room_reserved (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, room_id INT NOT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_ED442B06B83297E7 (reservation_id), INDEX IDX_ED442B0654177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE room_reserved ADD CONSTRAINT FK_ED442B0654177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_reserved ADD CONSTRAINT FK_ED442B06B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495554177093');
        $this->addSql('DROP INDEX IDX_42C8495554177093 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP room_id');
    }
}
