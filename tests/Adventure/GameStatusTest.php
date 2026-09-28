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
        $room = $this->createConfiguredMock(Room::class, [
            'getId' => 1,
            'getName' => 'Hamnen',
            'getDescription' => 'En hamn.',
        ]);
        $barrel = $this->createConfiguredMock(Item::class, [
            'getId' => 3, 'getName' => 'tunnan', 'getRoom' => $room, 'isStartsHidden' => false,
        ]);
        $purse = $this->createConfiguredMock(Item::class, [
            'getId' => 4, 'getName' => 'penningpungen', 'getRoom' => $room, 'isStartsHidden' => true,
        ]);
        $passage = $this->createConfiguredMock(Passage::class, [
            'getDirection' => 'east', 'getVerb' => 'Gå', 'isStartsLocked' => true,
        ]);

        $roomsMock = $this->createConfiguredMock(RoomRepository::class, ['find' => $room]);
        $passagesMock = $this->createConfiguredMock(PassageRepository::class, ['findBy' => [$passage]]);
        $itemsMock = $this->createMock(ItemRepository::class);
        $itemsMock->method('findBy')->willReturn([$barrel, $purse]);
        $itemsMock->method('find')->willReturn($barrel);

        $game = new GameSession('Test Testsson', $room);
        $game->getBackpack()->add($barrel);

        $status = new GameStatus($roomsMock, $passagesMock, $itemsMock);

        $this->assertEquals([
            'player' => 'Test Testsson',
            'moves' => 0,
            'won' => false,
            'room' => 'Hamnen',
            'description' => 'En hamn.',
            'items' => [],
            'exits' => [['direction' => 'east', 'verb' => 'Gå', 'open' => false]],
            'backpack' => ['tunnan'],
        ], $status->describe($game));
    }
}
