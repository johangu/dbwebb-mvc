<?php

namespace App\Tests\Adventure;

use App\Entity\Highscore;
use App\Entity\Hotspot;
use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;

class WorldLoaderTest extends WorldLoaderTestCase
{
    public function testLoadPersistsAllContent(): void
    {
        $persisted = $this->loadWorld();

        $this->assertCount(4, $this->ofClass($persisted, Room::class));
        $this->assertCount(6, $this->ofClass($persisted, Passage::class));
        $this->assertCount(24, $this->ofClass($persisted, Item::class));
        $this->assertCount(29, $this->ofClass($persisted, Hotspot::class));
        $this->assertCount(6, $this->ofClass($persisted, Interaction::class));
    }

    public function testLoadRemovesExistingContent(): void
    {
        $roomMock = $this->createMock(Room::class);
        $removed = [];

        $this->loadWorld([Room::class => [$roomMock]], $removed);

        $this->assertContains($roomMock, $removed);
    }

    public function testLoadKeepsHighscores(): void
    {
        $highscoreMock = $this->createMock(Highscore::class);
        $removed = [];

        $this->loadWorld([Highscore::class => [$highscoreMock]], $removed);

        $this->assertNotContains($highscoreMock, $removed);
    }

    public function testEveryItemHasHotspotInItsRoom(): void
    {
        $persisted = $this->loadWorld();
        $hotspots = $this->ofClass($persisted, Hotspot::class);

        foreach ($this->ofClass($persisted, Item::class) as $item) {
            $matching = array_filter(
                $hotspots,
                fn (Hotspot $hotspot) => $hotspot->getItem() === $item && $hotspot->getRoom() === $item->getRoom()
            );
            $this->assertNotEmpty($matching, "No hotspot for {$item->getName()}");
        }
    }

    public function testHotspotsPointAtOneThingInsideTheImage(): void
    {
        foreach ($this->ofClass($this->loadWorld(), Hotspot::class) as $hotspot) {
            $this->assertTrue(($hotspot->getItem() === null) !== ($hotspot->getPassage() === null));
            $this->assertGreaterThanOrEqual(0, $hotspot->getLeftPercent());
            $this->assertGreaterThanOrEqual(0, $hotspot->getTopPercent());
            $this->assertLessThanOrEqual(100, $hotspot->getLeftPercent() + $hotspot->getWidthPercent());
            $this->assertLessThanOrEqual(100, $hotspot->getTopPercent() + $hotspot->getHeightPercent());
        }
    }

    public function testOnlyRevealedItemsHaveImages(): void
    {
        $items = $this->ofClass($this->loadWorld(), Item::class);
        $withImage = array_filter($items, fn (Item $item) => $item->getImage() !== null);

        $this->assertCount(3, $withImage);
        foreach ($withImage as $item) {
            $this->assertTrue($item->isStartsHidden());
        }
    }
}
