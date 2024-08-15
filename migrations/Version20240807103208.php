<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240807103208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP INDEX UNIQ_42C8495554177093, ADD INDEX IDX_42C8495554177093 (room_id)');
        $this->addSql('ALTER TABLE reservation DROP INDEX UNIQ_42C84955A76ED395, ADD INDEX IDX_42C84955A76ED395 (user_id)');
        $this->addSql('ALTER TABLE room DROP images');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP INDEX IDX_42C8495554177093, ADD UNIQUE INDEX UNIQ_42C8495554177093 (room_id)');
        $this->addSql('ALTER TABLE reservation DROP INDEX IDX_42C84955A76ED395, ADD UNIQUE INDEX UNIQ_42C84955A76ED395 (user_id)');
        $this->addSql('ALTER TABLE room ADD images JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
