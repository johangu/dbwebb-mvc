<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260927171320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE hotspot (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, item_id INTEGER DEFAULT NULL, passage_id INTEGER DEFAULT NULL, left_percent DOUBLE PRECISION NOT NULL, top_percent DOUBLE PRECISION NOT NULL, width_percent DOUBLE PRECISION NOT NULL, height_percent DOUBLE PRECISION NOT NULL, CONSTRAINT FK_48B3831354177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_48B38313126F525E FOREIGN KEY (item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_48B38313DCC6487D FOREIGN KEY (passage_id) REFERENCES passage (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_48B3831354177093 ON hotspot (room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_48B38313126F525E ON hotspot (item_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_48B38313DCC6487D ON hotspot (passage_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD COLUMN image VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE hotspot
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__item AS SELECT id, room_id, name, description, starts_hidden, pickable FROM item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, starts_hidden BOOLEAN NOT NULL, pickable BOOLEAN NOT NULL, CONSTRAINT FK_1F1B251E54177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO item (id, room_id, name, description, starts_hidden, pickable) SELECT id, room_id, name, description, starts_hidden, pickable FROM __temp__item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251E54177093 ON item (room_id)
        SQL);
    }
}
