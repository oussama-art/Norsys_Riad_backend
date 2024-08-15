<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240807082414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP INDEX IDX_42C8495554177093, ADD UNIQUE INDEX UNIQ_42C8495554177093 (room_id)');
        $this->addSql('ALTER TABLE reservation ADD firstname VARCHAR(255) DEFAULT NULL, ADD lastname VARCHAR(255) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD tel VARCHAR(20) DEFAULT NULL, CHANGE total_price total_price NUMERIC(10, 2) DEFAULT NULL, CHANGE discount discount NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42C84955A76ED395 ON reservation (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP INDEX UNIQ_42C8495554177093, ADD INDEX IDX_42C8495554177093 (room_id)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('DROP INDEX UNIQ_42C84955A76ED395 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP firstname, DROP lastname, DROP email, DROP tel, CHANGE total_price total_price NUMERIC(10, 2) NOT NULL, CHANGE discount discount NUMERIC(10, 2) NOT NULL');
    }
}
