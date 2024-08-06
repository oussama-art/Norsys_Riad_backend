<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240802082325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        // Check if the table exists before attempting to create it
        if (!$schemaManager->tablesExist('reservation')) {
            $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_RESERVATION_ROOM_ID FOREIGN KEY (room_id) REFERENCES room (id)');
            $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_RESERVATION_USER_ID FOREIGN KEY (user_id) REFERENCES user (id)');
        }
    }

    public function down(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        // Check if the table exists before attempting to drop it
        if ($schemaManager->tablesExist('reservation')) {
            $this->addSql('DROP TABLE reservation');
        }
    }
}
