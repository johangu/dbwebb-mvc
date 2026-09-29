<?php

namespace App\Tests\Adventure;

use App\Adventure\Progress;
use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use PHPUnit\Framework\TestCase;

class ProgressTest extends TestCase
{
    public function testNewProgressHasNothingDone(): void
    {
        $progress = new Progress();

        $this->assertFalse($progress->isRevealed($this->createConfiguredMock(Item::class, ['getId' => 3])));
        $this->assertFalse($progress->isUnlocked($this->createConfiguredMock(Passage::class, ['getId' => 10])));
        $this->assertFalse($progress->isConsumed($this->createConfiguredMock(Item::class, ['getId' => 3])));
        $this->assertNull($progress->getRoomImage($this->createConfiguredMock(Room::class, ['getId' => 1])));
    }

    public function testRecordInteraction(): void
    {
        $room = $this->createConfiguredMock(Room::class, ['getId' => 1]);
        $purse = $this->createConfiguredMock(Item::class, ['getId' => 4]);
        $passage = $this->createConfiguredMock(Passage::class, ['getId' => 10]);
        $interaction = $this->createConfiguredMock(Interaction::class, [
            'getId' => 20,
            'getRoom' => $room,
            'getRevealsItem' => $purse,
            'getUnlocksPassage' => $passage,
            'getChangesRoomImage' => 'skeppet_2.webp',
        ]);

        $progress = new Progress();
        $progress->record($interaction);

        $this->assertTrue($progress->isRevealed($purse));
        $this->assertTrue($progress->isUnlocked($passage));
        $this->assertEquals('skeppet_2.webp', $progress->getRoomImage($room));
        $this->assertFalse($progress->isAvailable($interaction));
    }

    public function testInteractionRequiresEarlierInteraction(): void
    {
        $anchor = $this->createConfiguredMock(Interaction::class, ['getId' => 22]);
        $wheel = $this->createConfiguredMock(Interaction::class, ['getId' => 23, 'getRequiredInteraction' => $anchor]);

        $progress = new Progress();
        $this->assertFalse($progress->isAvailable($wheel));

        $progress->record($anchor);
        $this->assertTrue($progress->isAvailable($wheel));
    }

    public function testConsumeItem(): void
    {
        $purse = $this->createConfiguredMock(Item::class, ['getId' => 4]);

        $progress = new Progress();
        $progress->consume($purse);

        $this->assertTrue($progress->isConsumed($purse));
    }

    public function testJsonSerialize(): void
    {
        $progress = new Progress();
        $progress->consume($this->createConfiguredMock(Item::class, ['getId' => 4]));

        $json = json_encode($progress);
        $expectedJson = json_encode([
            'revealedItemIds' => [],
            'unlockedPassageIds' => [],
            'doneInteractionIds' => [],
            'consumedItemIds' => [4],
            'roomImages' => [],
        ]);

        $this->assertNotFalse($json);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($json);
        $this->assertJson($expectedJson);
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
