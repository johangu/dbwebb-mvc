<?php

namespace App\Tests\Adventure;

use App\Adventure\GameSession;
use App\Adventure\GameStatus;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use App\Repository\ItemRepository;
use App\Repository\PassageRepository;
use App\Repository\RoomRepository;
use PHPUnit\Framework\TestCase;

class GameStatusTest extends TestCase
{
    public function testDescribeGame(): void
    {
        $roomMock = $this->createConfiguredMock(Room::class, [
            'getId' => 1,
            'getName' => 'Hamnen',
            'getDescription' => 'En hamn.',
            'getImage' => 'hamnen.webp',
        ]);
        $barrelMock = $this->createConfiguredMock(Item::class, [
            'getId' => 3, 'getName' => 'tunnan', 'getRoom' => $roomMock, 'isStartsHidden' => false,
        ]);
        $purseMock = $this->createConfiguredMock(Item::class, [
            'getId' => 4, 'getName' => 'penningpungen', 'getRoom' => $roomMock, 'isStartsHidden' => true,
        ]);
        $passageMock = $this->createConfiguredMock(Passage::class, [
            'getDirection' => 'east', 'getVerb' => 'Gå', 'isStartsLocked' => true,
        ]);

        $roomsMock = $this->createConfiguredMock(RoomRepository::class, ['find' => $roomMock]);
        $passagesMock = $this->createConfiguredMock(PassageRepository::class, ['findBy' => [$passageMock]]);
        $itemsMock = $this->createMock(ItemRepository::class);
        $itemsMock->method('findBy')->willReturn([$barrelMock, $purseMock]);
        $itemsMock->method('find')->willReturn($barrelMock);

        $game = new GameSession('Test Testsson', $roomMock);
        $game->getBackpack()->add($barrelMock);

        $status = new GameStatus($roomsMock, $passagesMock, $itemsMock);

        $this->assertEquals([
            'player' => 'Test Testsson',
            'moves' => 0,
            'won' => false,
            'room' => 'Hamnen',
            'description' => 'En hamn.',
            'image' => 'hamnen.webp',
            'items' => [],
            'exits' => [['direction' => 'east', 'verb' => 'Gå', 'open' => false]],
            'backpack' => ['tunnan'],
        ], $status->describe($game));
    }
}
