<?php

namespace App\Tests\Controller;

use App\Entity\Room;

class ProjectControllerTest extends ProjectWebTestCase
{
    public function testLandingPage(): void
    {
        $this->client->request('GET', '/proj');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Piratäventyret');
        $this->assertSelectorExists('a[href="/proj/play"]');
        $this->assertSelectorExists('a[href="/proj/highscore"]');
        $this->assertSelectorExists('a[href="/proj/cheat"]');
    }

    public function testAboutPage(): void
    {
        $this->client->request('GET', '/proj/about');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/proj/about/database"]');
    }

    public function testDatabasePage(): void
    {
        $this->client->request('GET', '/proj/about/database');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('img.er-diagram');
    }

    public function testCheatSheet(): void
    {
        $crawler = $this->client->request('GET', '/proj/cheat');

        $this->assertResponseIsSuccessful();
        $this->assertCount(13, $crawler->filter('ol li'));
    }

    public function testResetLoadsWorldAndEndsGame(): void
    {
        $this->startGame();
        $this->client->request('POST', '/proj/reset');

        $this->assertResponseRedirects('/proj');
        $this->assertCount(4, $this->entityManager->getRepository(Room::class)->findAll());

        $this->client->request('GET', '/proj/play');
        $this->assertSelectorExists('form.start-form');
    }
}
