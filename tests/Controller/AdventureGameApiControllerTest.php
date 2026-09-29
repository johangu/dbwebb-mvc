<?php

namespace App\Tests\Controller;

class AdventureGameApiControllerTest extends ProjectWebTestCase
{
    public function testGameNotStarted(): void
    {
        $this->client->request('GET', '/proj/api/game');
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('POST', '/proj/api/examine/tunnan');
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('POST', '/proj/api/move/east');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testStartRequiresName(): void
    {
        $this->client->request('POST', '/proj/api/start', ['name' => '']);

        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('A name is needed to play', $this->json()['error']);
    }

    public function testStartGame(): void
    {
        $this->client->request('POST', '/proj/api/start', ['name' => 'API-pirat']);
        $this->client->request('GET', '/proj/api/game');
        $game = $this->json();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('API-pirat', $game['player']);
        $this->assertEquals('Hamnen', $game['room']);
        $this->assertNotContains('penningpungen', $this->jsonArray('items'));
    }

    public function testExamineAndTake(): void
    {
        $this->client->request('POST', '/proj/api/start', ['name' => 'API-pirat']);
        $this->client->request('POST', '/proj/api/examine/tunnan');
        $this->assertContains('penningpungen', $this->jsonArray('items'));

        $this->client->request('POST', '/proj/api/take/penningpungen');
        $this->assertEquals(['penningpungen'], $this->jsonArray('backpack'));
    }

    public function testUseAndMove(): void
    {
        $this->client->request('POST', '/proj/api/start', ['name' => 'API-pirat']);
        $this->client->request('POST', '/proj/api/examine/tunnan');
        $this->client->request('POST', '/proj/api/take/penningpungen');
        $this->client->request('POST', '/proj/api/move/north');
        $this->assertEquals('Krogen', $this->json()['room']);

        $this->client->request('POST', '/proj/api/use/penningpungen/sjömännen');
        $this->assertEquals(
            'Sjömännen tar gärna emot pengarna och går med på att bli din besättning.',
            $this->json()['message']
        );
        $this->assertEquals([], $this->jsonArray('backpack'));
    }

    public function testUnknownItemOrDirection(): void
    {
        $this->client->request('POST', '/proj/api/start', ['name' => 'API-pirat']);

        $this->client->request('POST', '/proj/api/examine/finnsinte');
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('POST', '/proj/api/use/finnsinte/tunnan');
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('POST', '/proj/api/move/up');
        $this->assertResponseStatusCodeSame(404);
    }
}
