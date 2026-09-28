<?php

namespace App\Tests\Adventure;

use App\Adventure\GameSession;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use PHPUnit\Framework\TestCase;

class GameSessionTest extends TestCase
{
    /**
     * Create a game session that starts in a room with id 1.
     */
    private function createSession(): GameSession
    {
        $roomMock = $this->createConfiguredMock(Room::class, ['getId' => 1]);

        return new GameSession('Test Testsson', $roomMock);
    }

    /**
     * Create a passage between two rooms.
     *
     * @param array<string, mixed> $config Methods to override on the passage mock
     */
    private function createPassage(int $fromRoomId, int $toRoomId, array $config = []): Passage
    {
        return $this->createConfiguredMock(Passage::class, array_merge([
            'getId' => 10,
            'getFromRoom' => $this->createConfiguredMock(Room::class, ['getId' => $fromRoomId]),
            'getToRoom' => $this->createConfiguredMock(Room::class, ['getId' => $toRoomId]),
            'isStartsLocked' => false,
        ], $config));
    }

    public function testNewSession(): void
    {
        $session = $this->createSession();

        $this->assertEquals('Test Testsson', $session->getPlayerName());
        $this->assertEquals(1, $session->getCurrentRoomId());
        $this->assertEquals(0, $session->getMoves());
        $this->assertEquals([], $session->getBackpack()->getItemIds());
    }

    public function testItemThatDoesNotStartHiddenIsVisible(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3, 'isStartsHidden' => false]);

        $this->assertTrue($this->createSession()->isItemVisible($itemMock));
    }

    public function testItemThatStartsHiddenIsNotVisible(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3, 'isStartsHidden' => true]);

        $this->assertFalse($this->createSession()->isItemVisible($itemMock));
    }

    public function testItemInBackpackIsNotVisible(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3, 'isStartsHidden' => false]);
        $session = $this->createSession();
        $session->getBackpack()->add($itemMock);

        $this->assertFalse($session->isItemVisible($itemMock));
    }

    public function testPassageThatStartsLockedIsNotOpen(): void
    {
        $session = $this->createSession();

        $this->assertTrue($session->isPassageOpen($this->createPassage(1, 2)));
        $this->assertFalse($session->isPassageOpen($this->createPassage(1, 2, ['isStartsLocked' => true])));
    }

    public function testMoveThroughOpenPassage(): void
    {
        $session = $this->createSession();

        $this->assertTrue($session->move($this->createPassage(1, 2)));
        $this->assertEquals(2, $session->getCurrentRoomId());
        $this->assertEquals(1, $session->getMoves());
    }

    public function testMoveThroughLockedPassageCountsAsMove(): void
    {
        $session = $this->createSession();

        $this->assertFalse($session->move($this->createPassage(1, 2, ['isStartsLocked' => true])));
        $this->assertEquals(1, $session->getCurrentRoomId());
        $this->assertEquals(1, $session->getMoves());
    }

    public function testMoveFromAnotherRoomFails(): void
    {
        $session = $this->createSession();

        $this->assertFalse($session->move($this->createPassage(3, 2)));
        $this->assertEquals(1, $session->getCurrentRoomId());
        $this->assertEquals(1, $session->getMoves());
    }

    public function testJsonSerialize(): void
    {
        $session = $this->createSession();

        $json = json_encode($session);
        $expectedJson = json_encode([
            'playerName' => 'Test Testsson',
            'currentRoomId' => 1,
            'backpack' => ['itemIds' => []],
            'revealedItemIds' => [],
            'unlockedPassageIds' => [],
            'moves' => 0,
        ]);

        $this->assertNotFalse($json);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($json);
        $this->assertJson($expectedJson);
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
