<?php

namespace App\Tests\Adventure;

use App\Adventure\GameSession;
use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use PHPUnit\Framework\TestCase;

class GameSessionActionTest extends TestCase
{
    private Room $room;

    protected function setUp(): void
    {
        $this->room = $this->createConfiguredMock(Room::class, ['getId' => 1]);
    }

    /**
     * Create an item in the room with id 1.
     *
     * @param array<string, mixed> $config Methods to override on the item mock
     */
    private function createItem(int $itemId, array $config = []): Item
    {
        return $this->createConfiguredMock(Item::class, array_merge([
            'getId' => $itemId,
            'getRoom' => $this->room,
            'isStartsHidden' => false,
            'isPickable' => false,
        ], $config));
    }

    /**
     * Create an interaction in the room with id 1.
     *
     * @param array<string, mixed> $config Methods to override on the interaction mock
     */
    private function createInteraction(int $interactionId, Item $target, array $config = []): Interaction
    {
        return $this->createConfiguredMock(Interaction::class, array_merge([
            'getId' => $interactionId,
            'getRoom' => $this->room,
            'getTarget' => $target,
            'getUsedItem' => null,
            'getRequiredInteraction' => null,
            'getRevealsItem' => null,
            'getUnlocksPassage' => null,
            'isConsumesUsedItem' => false,
            'isWins' => false,
        ], $config));
    }

    public function testExamineRevealsHiddenItem(): void
    {
        $barrel = $this->createItem(3);
        $purse = $this->createItem(4, ['isStartsHidden' => true]);
        $interaction = $this->createInteraction(20, $barrel, ['getRevealsItem' => $purse]);
        $game = new GameSession('Test Testsson', $this->room);

        $this->assertSame($interaction, $game->examine($barrel, [$interaction]));
        $this->assertTrue($game->isItemVisible($purse));
        $this->assertEquals(1, $game->getMoves());
    }

    public function testExamineOnlyPerformsInteractionOnce(): void
    {
        $barrel = $this->createItem(3);
        $interaction = $this->createInteraction(20, $barrel);
        $game = new GameSession('Test Testsson', $this->room);
        $game->examine($barrel, [$interaction]);

        $this->assertNull($game->examine($barrel, [$interaction]));
        $this->assertEquals(2, $game->getMoves());
    }

    public function testExamineItemWithoutInteraction(): void
    {
        $game = new GameSession('Test Testsson', $this->room);

        $this->assertNull($game->examine($this->createItem(3), []));
        $this->assertEquals(1, $game->getMoves());
    }

    public function testExamineItemInAnotherRoom(): void
    {
        $otherRoom = $this->createConfiguredMock(Room::class, ['getId' => 2]);
        $barrel = $this->createItem(3, ['getRoom' => $otherRoom]);
        $game = new GameSession('Test Testsson', $this->room);

        $this->assertNull($game->examine($barrel, [$this->createInteraction(20, $barrel)]));
    }

    public function testTakeItem(): void
    {
        $purse = $this->createItem(4, ['isPickable' => true]);
        $game = new GameSession('Test Testsson', $this->room);

        $this->assertTrue($game->take($purse));
        $this->assertTrue($game->getBackpack()->has($purse));
        $this->assertFalse($game->isItemVisible($purse));
        $this->assertEquals(1, $game->getMoves());
    }

    public function testTakeHiddenOrFixedItemFails(): void
    {
        $game = new GameSession('Test Testsson', $this->room);

        $this->assertFalse($game->take($this->createItem(4, ['isPickable' => true, 'isStartsHidden' => true])));
        $this->assertFalse($game->take($this->createItem(3)));
        $this->assertEquals([], $game->getBackpack()->getItemIds());
        $this->assertEquals(2, $game->getMoves());
    }

    public function testUseRequiresItemInBackpack(): void
    {
        $purse = $this->createItem(4, ['isPickable' => true]);
        $sailors = $this->createItem(5);
        $interaction = $this->createInteraction(21, $sailors, ['getUsedItem' => $purse]);
        $game = new GameSession('Test Testsson', $this->room);

        $this->assertNull($game->use($purse, $sailors, [$interaction]));

        $game->take($purse);
        $this->assertSame($interaction, $game->use($purse, $sailors, [$interaction]));
    }

    public function testUseConsumesItem(): void
    {
        $purse = $this->createItem(4, ['isPickable' => true]);
        $sailors = $this->createItem(5);
        $interaction = $this->createInteraction(21, $sailors, ['getUsedItem' => $purse, 'isConsumesUsedItem' => true]);
        $game = new GameSession('Test Testsson', $this->room);
        $game->take($purse);
        $game->use($purse, $sailors, [$interaction]);

        $this->assertFalse($game->getBackpack()->has($purse));
        $this->assertFalse($game->isItemVisible($purse));
    }

    public function testRequiredInteractionUnlocksPassage(): void
    {
        $crew = $this->createItem(6, ['isPickable' => true]);
        $anchor = $this->createItem(7);
        $wheel = $this->createItem(8);
        $passage = $this->createConfiguredMock(Passage::class, ['getId' => 10, 'isStartsLocked' => true]);
        $raiseAnchor = $this->createInteraction(22, $anchor, ['getUsedItem' => $crew]);
        $takeWheel = $this->createInteraction(23, $wheel, [
            'getRequiredInteraction' => $raiseAnchor,
            'getUnlocksPassage' => $passage,
        ]);
        $game = new GameSession('Test Testsson', $this->room);
        $game->take($crew);

        $this->assertNull($game->examine($wheel, [$takeWheel]));
        $this->assertFalse($game->isPassageOpen($passage));

        $game->use($crew, $anchor, [$raiseAnchor]);
        $this->assertSame($takeWheel, $game->examine($wheel, [$takeWheel]));
        $this->assertTrue($game->isPassageOpen($passage));
    }

    public function testWinningInteraction(): void
    {
        $map = $this->createItem(9, ['isPickable' => true]);
        $horizon = $this->createItem(10);
        $interaction = $this->createInteraction(24, $horizon, ['getUsedItem' => $map, 'isWins' => true]);
        $game = new GameSession('Test Testsson', $this->room);
        $game->take($map);

        $this->assertFalse($game->hasWon());
        $game->use($map, $horizon, [$interaction]);
        $this->assertTrue($game->hasWon());
    }
}
