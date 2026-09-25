<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925114352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE highscore (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, moves INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE interaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, target_id INTEGER NOT NULL, used_item_id INTEGER DEFAULT NULL, required_interaction_id INTEGER DEFAULT NULL, reveals_item_id INTEGER DEFAULT NULL, unlocks_passage_id INTEGER DEFAULT NULL, wins BOOLEAN NOT NULL, message CLOB NOT NULL, CONSTRAINT FK_378DFDA754177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA7158E0B66 FOREIGN KEY (target_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA795B877AF FOREIGN KEY (used_item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA753E362BE FOREIGN KEY (required_interaction_id) REFERENCES interaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA74B3D0F83 FOREIGN KEY (reveals_item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA76EC5D8A9 FOREIGN KEY (unlocks_passage_id) REFERENCES passage (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378DFDA754177093 ON interaction (room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378DFDA7158E0B66 ON interaction (target_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378DFDA795B877AF ON interaction (used_item_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378DFDA753E362BE ON interaction (required_interaction_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378DFDA74B3D0F83 ON interaction (reveals_item_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378DFDA76EC5D8A9 ON interaction (unlocks_passage_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, hidden BOOLEAN NOT NULL, pickable BOOLEAN NOT NULL, CONSTRAINT FK_1F1B251E54177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1F1B251E54177093 ON item (room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE passage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_room_id INTEGER NOT NULL, to_room_id INTEGER NOT NULL, direction VARCHAR(255) NOT NULL, verb VARCHAR(255) NOT NULL, locked BOOLEAN NOT NULL, CONSTRAINT FK_2B258F67D249933C FOREIGN KEY (from_room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2B258F67DA8F4D66 FOREIGN KEY (to_room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B258F67D249933C ON passage (from_room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B258F67DA8F4D66 ON passage (to_room_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, image VARCHAR(255) NOT NULL)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE highscore
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE interaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE passage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE room
        SQL);
    }
}
