<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260926084524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__item AS SELECT id, room_id, name, description, hidden, pickable FROM item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, starts_hidden BOOLEAN NOT NULL, pickable BOOLEAN NOT NULL, CONSTRAINT FK_1F1B251E54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO item (id, room_id, name, description, starts_hidden, pickable) SELECT id, room_id, name, description, hidden, pickable FROM __temp__item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251E54177093 ON item (room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__passage AS SELECT id, from_room_id, to_room_id, direction, verb, locked FROM passage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE passage
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE passage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_room_id INTEGER NOT NULL, to_room_id INTEGER NOT NULL, direction VARCHAR(255) NOT NULL, verb VARCHAR(255) NOT NULL, starts_locked BOOLEAN NOT NULL, CONSTRAINT FK_2B258F67D249933C FOREIGN KEY (from_room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2B258F67DA8F4D66 FOREIGN KEY (to_room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO passage (id, from_room_id, to_room_id, direction, verb, starts_locked) SELECT id, from_room_id, to_room_id, direction, verb, locked FROM __temp__passage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__passage
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B258F67DA8F4D66 ON passage (to_room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B258F67D249933C ON passage (from_room_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__item AS SELECT id, room_id, name, description, starts_hidden, pickable FROM item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, hidden BOOLEAN NOT NULL, pickable BOOLEAN NOT NULL, CONSTRAINT FK_1F1B251E54177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO item (id, room_id, name, description, hidden, pickable) SELECT id, room_id, name, description, starts_hidden, pickable FROM __temp__item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__item
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251E54177093 ON item (room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__passage AS SELECT id, from_room_id, to_room_id, direction, verb, starts_locked FROM passage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE passage
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE passage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_room_id INTEGER NOT NULL, to_room_id INTEGER NOT NULL, direction VARCHAR(255) NOT NULL, verb VARCHAR(255) NOT NULL, locked BOOLEAN NOT NULL, CONSTRAINT FK_2B258F67D249933C FOREIGN KEY (from_room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2B258F67DA8F4D66 FOREIGN KEY (to_room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO passage (id, from_room_id, to_room_id, direction, verb, locked) SELECT id, from_room_id, to_room_id, direction, verb, starts_locked FROM __temp__passage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__passage
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B258F67D249933C ON passage (from_room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B258F67DA8F4D66 ON passage (to_room_id)
        SQL);
    }
}
