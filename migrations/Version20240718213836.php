<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718213836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD login_time DATETIME DEFAULT NULL, DROP failed_login_attempts, DROP lockout_time, DROP lockout_time_end');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD failed_login_attempts INT NOT NULL, ADD lockout_time_end DATETIME DEFAULT NULL, CHANGE login_time lockout_time DATETIME DEFAULT NULL');
    }
}
