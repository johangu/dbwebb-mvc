<?php

namespace App\Tests\Controller;

class AdventureControllerTest extends ProjectWebTestCase
{
    public function testStartPageWithoutGame(): void
    {
        $this->client->request('GET', '/proj/play');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form.start-form input[name="name"]');
    }

    public function testStartRequiresName(): void
    {
        $this->startGame('  ');

        $this->assertResponseRedirects('/proj/play');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message', 'Du måste ange ett namn');
    }

    public function testStartGameInFirstRoom(): void
    {
        $this->startGame();
        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Hamnen');
        $this->assertSelectorTextContains('.moves', 'Testpirat, antal drag: 0');
    }

    public function testExamineShowsMessage(): void
    {
        $this->startGame();
        $this->clickItem('tunnan', 'examine');

        $this->assertSelectorTextContains('.scene-message', 'hittar en penningpung');
        $this->assertSelectorExists('button[aria-label="Penningpungen"]');
    }

    public function testTakeItemPutsItInBackpack(): void
    {
        $this->startGame();
        $this->clickItem('tunnan', 'examine');
        $this->clickItem('penningpungen', 'take');

        $this->assertSelectorTextContains('.scene-message', 'Du tar penningpungen.');
        $this->assertSelectorExists('.inventory-slot a[title="Penningpungen"]');
    }

    public function testSelectItemToUse(): void
    {
        $this->startGame();
        $this->clickItem('tunnan', 'examine');
        $this->clickItem('penningpungen', 'take');
        $this->client->request('GET', '/proj/play?verb=use&item=' . $this->itemId('penningpungen'));

        $this->assertSelectorTextContains('.sentence', 'Använd penningpungen på');
        $this->assertSelectorExists('.inventory-selected');
    }

    public function testUnknownVerbFallsBackToExamine(): void
    {
        $this->startGame();
        $this->client->request('GET', '/proj/play?verb=dance');

        $this->assertSelectorExists('.scene.verb-examine');
    }

    public function testMoveThroughPassages(): void
    {
        $this->startGame();
        $this->move('Hamnen', 'east');
        $this->move('Skeppet', 'east');

        $this->assertSelectorTextContains('h1', 'Skeppet');
        $this->assertSelectorTextContains('.scene-message', 'Det går inte att ta sig dit än.');
    }

    public function testRestartEndsGame(): void
    {
        $this->startGame();
        $this->client->request('POST', '/proj/restart');
        $this->client->followRedirect();

        $this->assertSelectorExists('form.start-form');
    }

    public function testActionsWithoutGameRedirect(): void
    {
        $this->client->request('POST', '/proj/click', ['hotspot' => $this->hotspotFor('tunnan')]);
        $this->assertResponseRedirects('/proj/play');

        $this->client->request('POST', '/proj/move', ['passage' => $this->passageFrom('Hamnen', 'east')]);
        $this->assertResponseRedirects('/proj/play');
    }
}
