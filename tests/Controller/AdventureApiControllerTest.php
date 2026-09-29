<?php

namespace App\Tests\Controller;

use App\Entity\Room;

class AdventureApiControllerTest extends ProjectWebTestCase
{
    public function testApiPage(): void
    {
        $crawler = $this->client->request('GET', '/proj/api');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(5, $crawler->filter('.api-list li')->count());
    }

    public function testRooms(): void
    {
        $this->client->request('GET', '/proj/api/rooms');
        /** @var array<int, array{name: string, exits: array<mixed>}> $rooms */
        $rooms = $this->json();

        $this->assertResponseIsSuccessful();
        $this->assertCount(4, $rooms);
        $this->assertEquals('Hamnen', $rooms[0]['name']);
        $this->assertCount(2, $rooms[0]['exits']);
    }

    public function testRoom(): void
    {
        $room = $this->entityManager->getRepository(Room::class)->findOneBy(['name' => 'Krogen']);
        $this->client->request('GET', '/proj/api/rooms/' . $room?->getId());

        $this->assertResponseIsSuccessful();
        $this->assertEquals('Krogen', $this->json()['name']);
    }

    public function testRoomNotFound(): void
    {
        $this->client->request('GET', '/proj/api/rooms/99999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('Room not found', $this->json()['error']);
    }

    public function testHighscore(): void
    {
        $this->client->request('GET', '/proj/api/highscore');

        $this->assertResponseIsSuccessful();
        $this->assertEquals([], $this->json());
    }

    public function testReset(): void
    {
        $this->client->request('POST', '/proj/api/reset');

        $this->assertResponseIsSuccessful();
        $this->assertEquals(4, $this->json()['rooms']);
        $this->assertEquals(24, $this->json()['items']);
    }
}
