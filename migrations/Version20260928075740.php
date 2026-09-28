<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260928075740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE interaction ADD COLUMN consumes_used_item BOOLEAN DEFAULT 0 NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__interaction AS SELECT id, room_id, target_id, used_item_id, required_interaction_id, reveals_item_id, unlocks_passage_id, wins, message FROM interaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE interaction
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE interaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER NOT NULL, target_id INTEGER NOT NULL, used_item_id INTEGER DEFAULT NULL, required_interaction_id INTEGER DEFAULT NULL, reveals_item_id INTEGER DEFAULT NULL, unlocks_passage_id INTEGER DEFAULT NULL, wins BOOLEAN NOT NULL, message CLOB NOT NULL, CONSTRAINT FK_378DFDA754177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA7158E0B66 FOREIGN KEY (target_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA795B877AF FOREIGN KEY (used_item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA753E362BE FOREIGN KEY (required_interaction_id) REFERENCES interaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA74B3D0F83 FOREIGN KEY (reveals_item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_378DFDA76EC5D8A9 FOREIGN KEY (unlocks_passage_id) REFERENCES passage (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO interaction (id, room_id, target_id, used_item_id, required_interaction_id, reveals_item_id, unlocks_passage_id, wins, message) SELECT id, room_id, target_id, used_item_id, required_interaction_id, reveals_item_id, unlocks_passage_id, wins, message FROM __temp__interaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__interaction
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
    }
}
