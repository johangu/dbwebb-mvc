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
        $roomMock = $this->createConfiguredMock(Room::class, ['getId' => 1]);
        $purseMock = $this->createConfiguredMock(Item::class, ['getId' => 4]);
        $passageMock = $this->createConfiguredMock(Passage::class, ['getId' => 10]);
        $interactionMock = $this->createConfiguredMock(Interaction::class, [
            'getId' => 20,
            'getRoom' => $roomMock,
            'getRevealsItem' => $purseMock,
            'getUnlocksPassage' => $passageMock,
            'getChangesRoomImage' => 'skeppet_2.webp',
        ]);

        $progress = new Progress();
        $progress->record($interactionMock);

        $this->assertTrue($progress->isRevealed($purseMock));
        $this->assertTrue($progress->isUnlocked($passageMock));
        $this->assertEquals('skeppet_2.webp', $progress->getRoomImage($roomMock));
        $this->assertFalse($progress->isAvailable($interactionMock));
    }

    public function testInteractionRequiresEarlierInteraction(): void
    {
        $anchorMock = $this->createConfiguredMock(Interaction::class, ['getId' => 22]);
        $wheelMock = $this->createConfiguredMock(Interaction::class, ['getId' => 23, 'getRequiredInteraction' => $anchorMock]);

        $progress = new Progress();
        $this->assertFalse($progress->isAvailable($wheelMock));

        $progress->record($anchorMock);
        $this->assertTrue($progress->isAvailable($wheelMock));
    }

    public function testConsumeItem(): void
    {
        $purseMock = $this->createConfiguredMock(Item::class, ['getId' => 4]);

        $progress = new Progress();
        $progress->consume($purseMock);

        $this->assertTrue($progress->isConsumed($purseMock));
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
