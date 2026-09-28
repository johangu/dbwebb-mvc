<?php

namespace App\Tests\Adventure;

use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;

class WorldLoaderPuzzleTest extends WorldLoaderTestCase
{
    public function testOnlyPassageToTheSeaIsLocked(): void
    {
        $passages = $this->ofClass($this->loadWorld(), Passage::class);
        $locked = array_values(array_filter($passages, fn (Passage $passage) => $passage->isStartsLocked() === true));

        $this->assertCount(1, $locked);
        $this->assertEquals('Skeppet', $locked[0]->getFromRoom()?->getName());
        $this->assertEquals('Havet', $locked[0]->getToRoom()?->getName());
        $this->assertEquals('Segla', $locked[0]->getVerb());
    }

    public function testWheelRequiresAnchorAndUnlocksTheSea(): void
    {
        $interactions = $this->ofClass($this->loadWorld(), Interaction::class);
        $wheel = array_values(array_filter(
            $interactions,
            fn (Interaction $interaction) => $interaction->getTarget()?->getName() === 'ratten'
        ));

        $this->assertCount(1, $wheel);
        $this->assertEquals('ankaret', $wheel[0]->getRequiredInteraction()?->getTarget()?->getName());
        $this->assertTrue($wheel[0]->getUnlocksPassage()?->isStartsLocked());
    }

    public function testOnlyTreasureMapOnHorizonWins(): void
    {
        $interactions = $this->ofClass($this->loadWorld(), Interaction::class);
        $wins = array_values(array_filter($interactions, fn (Interaction $interaction) => $interaction->isWins() === true));

        $this->assertCount(1, $wins);
        $this->assertEquals('horisonten', $wins[0]->getTarget()?->getName());
        $this->assertEquals('skattkartan', $wins[0]->getUsedItem()?->getName());
    }

    public function testOnlyPayingTheSailorsConsumesTheItem(): void
    {
        $interactions = $this->ofClass($this->loadWorld(), Interaction::class);
        $consuming = array_values(array_filter(
            $interactions,
            fn (Interaction $interaction) => $interaction->isConsumesUsedItem() === true
        ));

        $this->assertCount(1, $consuming);
        $this->assertEquals('sjömännen', $consuming[0]->getTarget()?->getName());
        $this->assertEquals('penningpungen', $consuming[0]->getUsedItem()?->getName());
    }

    public function testHiddenItemsCanBePickedUpAndAreRevealed(): void
    {
        $persisted = $this->loadWorld();
        $hidden = array_filter($this->ofClass($persisted, Item::class), fn (Item $item) => $item->isStartsHidden() === true);
        $revealed = array_map(
            fn (Interaction $interaction) => $interaction->getRevealsItem(),
            $this->ofClass($persisted, Interaction::class)
        );

        $this->assertCount(3, $hidden);
        foreach ($hidden as $item) {
            $this->assertTrue($item->isPickable());
            $this->assertContains($item, $revealed);
        }
    }
}
