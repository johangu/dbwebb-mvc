<?php

namespace App\Tests\Controller;

use App\Entity\Highscore;

class AdventureWinTest extends ProjectWebTestCase
{
    /**
     * Play through the adventure following the cheat sheet.
     */
    private function playToTreasure(): void
    {
        $this->startGame();
        $this->clickItem('tunnan', 'examine');
        $this->clickItem('penningpungen', 'take');
        $this->move('Hamnen', 'north');
        $this->clickItem('tavlan', 'examine');
        $this->clickItem('skattkartan', 'take');
        $this->clickItem('sjömännen', 'use', 'penningpungen');
        $this->clickItem('besättningen', 'take');
        $this->move('Krogen', 'south');
        $this->move('Hamnen', 'east');
        $this->clickItem('ankarspelet', 'use', 'besättningen');
        $this->clickItem('ratten', 'examine');
        $this->move('Skeppet', 'east');
        $this->clickItem('horisonten', 'use', 'skattkartan');
    }

    public function testWinShowsWinScreen(): void
    {
        $this->playToTreasure();

        $this->assertSelectorTextContains('h1', 'Skatten');
        $this->assertSelectorTextContains('.sentence', 'Testpirat hittade skatten på 13 drag!');
        $this->assertSelectorTextContains('.scene-message', 'hittar skatten');
    }

    public function testWinIsRecordedOnce(): void
    {
        $this->playToTreasure();
        $this->clickItem('horisonten', 'use', 'skattkartan');

        $highscores = $this->entityManager->getRepository(Highscore::class)->findAll();
        $this->assertCount(1, $highscores);
        $this->assertEquals(13, $highscores[0]->getMoves());
    }

    public function testHighscoreList(): void
    {
        $this->playToTreasure();
        $crawler = $this->client->request('GET', '/proj/highscore');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('table.highscore tbody tr'));
        $this->assertSelectorTextContains('table.highscore tbody', 'Testpirat');
    }

    public function testEmptyHighscoreList(): void
    {
        $this->client->request('GET', '/proj/highscore');

        $this->assertSelectorTextContains('main', 'Ingen har hittat skatten än.');
    }

    public function testShipChangesImageWhenAnchorIsRaised(): void
    {
        $this->startGame();
        $this->clickItem('tunnan', 'examine');
        $this->clickItem('penningpungen', 'take');
        $this->move('Hamnen', 'north');
        $this->clickItem('sjömännen', 'use', 'penningpungen');
        $this->clickItem('besättningen', 'take');
        $this->move('Krogen', 'south');
        $this->move('Hamnen', 'east');
        $this->clickItem('ankarspelet', 'use', 'besättningen');

        $this->assertSelectorExists('img.scene-image[src$="skeppet_2.webp"]');
    }

    public function testGameFromOldWorldStartsOver(): void
    {
        $this->startGame();
        $this->entityManager->getConnection()->executeStatement('UPDATE room SET id = id + 100');
        $this->client->request('GET', '/proj/play');

        $this->assertResponseRedirects('/proj/play');
    }
}
