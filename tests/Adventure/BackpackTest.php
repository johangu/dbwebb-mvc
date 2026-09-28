<?php

namespace App\Tests\Adventure;

use App\Adventure\Backpack;
use App\Entity\Item;
use PHPUnit\Framework\TestCase;

class BackpackTest extends TestCase
{
    public function testNewBackpackIsEmpty(): void
    {
        $backpack = new Backpack();

        $this->assertEquals([], $backpack->getItemIds());
    }

    public function testAddItem(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3]);

        $backpack = new Backpack();
        $backpack->add($itemMock);

        $this->assertTrue($backpack->has($itemMock));
        $this->assertEquals([3], $backpack->getItemIds());
    }

    public function testAddSameItemTwice(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3]);

        $backpack = new Backpack();
        $backpack->add($itemMock);
        $backpack->add($itemMock);

        $this->assertCount(1, $backpack->getItemIds());
    }

    public function testRemoveItem(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3]);
        $otherMock = $this->createConfiguredMock(Item::class, ['getId' => 4]);

        $backpack = new Backpack();
        $backpack->add($itemMock);
        $backpack->add($otherMock);
        $backpack->remove($itemMock);

        $this->assertFalse($backpack->has($itemMock));
        $this->assertEquals([4], $backpack->getItemIds());
    }

    public function testDoesNotHaveOtherItem(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3]);
        $otherMock = $this->createConfiguredMock(Item::class, ['getId' => 4]);

        $backpack = new Backpack();
        $backpack->add($itemMock);

        $this->assertFalse($backpack->has($otherMock));
    }

    public function testJsonSerialize(): void
    {
        $itemMock = $this->createConfiguredMock(Item::class, ['getId' => 3]);

        $backpack = new Backpack();
        $backpack->add($itemMock);

        $json = json_encode($backpack);
        $expectedJson = json_encode([
            'itemIds' => [3],
        ]);

        $this->assertNotFalse($json);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($json);
        $this->assertJson($expectedJson);
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
