<?php

namespace App\Tests\Adventure;

use App\Adventure\WorldLoader;
use App\Entity\Highscore;
use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class WorldLoaderTest extends TestCase
{
    /**
     * Load the world with mocked Doctrine and return what was persisted.
     *
     * @param array<class-string, array<object>> $existing Entities already in the database, by class
     * @param array<object> $removed Collects the removed entities
     *
     * @return array<object> The persisted entities
     */
    private function loadWorld(array $existing = [], array &$removed = []): array
    {
        $persisted = [];

        $managerMock = $this->createMock(ObjectManager::class);
        $managerMock->method('getRepository')
            ->willReturnCallback(function (string $class) use ($existing) {
                $repositoryMock = $this->createMock(ObjectRepository::class);
                $repositoryMock->method('findAll')->willReturn($existing[$class] ?? []);

                return $repositoryMock;
            });
        $managerMock->method('persist')
            ->willReturnCallback(function (object $entity) use (&$persisted) {
                $persisted[] = $entity;
            });
        $managerMock->method('remove')
            ->willReturnCallback(function (object $entity) use (&$removed) {
                $removed[] = $entity;
            });

        $doctrineMock = $this->createMock(ManagerRegistry::class);
        $doctrineMock->method('getManager')->willReturn($managerMock);

        $loader = new WorldLoader($doctrineMock);
        $loader->load();

        return $persisted;
    }

    /**
     * Get the persisted entities of a given class.
     *
     * @template T of object
     *
     * @param array<object> $entities
     * @param class-string<T> $class
     *
     * @return array<T>
     */
    private function ofClass(array $entities, string $class): array
    {
        return array_values(array_filter($entities, fn (object $entity) => $entity instanceof $class));
    }

    public function testLoadPersistsAllContent(): void
    {
        $persisted = $this->loadWorld();

        $this->assertCount(4, $this->ofClass($persisted, Room::class));
        $this->assertCount(6, $this->ofClass($persisted, Passage::class));
        $this->assertCount(9, $this->ofClass($persisted, Item::class));
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

    public function testOnlyPassageToTheSeaIsLocked(): void
    {
        $passages = $this->ofClass($this->loadWorld(), Passage::class);
        $locked = array_values(array_filter($passages, fn (Passage $passage) => $passage->isLocked() === true));

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
        $this->assertTrue($wheel[0]->getUnlocksPassage()?->isLocked());
    }

    public function testOnlyTreasureMapOnHorizonWins(): void
    {
        $interactions = $this->ofClass($this->loadWorld(), Interaction::class);
        $wins = array_values(array_filter($interactions, fn (Interaction $interaction) => $interaction->isWins() === true));

        $this->assertCount(1, $wins);
        $this->assertEquals('horisonten', $wins[0]->getTarget()?->getName());
        $this->assertEquals('skattkartan', $wins[0]->getUsedItem()?->getName());
    }

    public function testHiddenItemsCanBePickedUpAndAreRevealed(): void
    {
        $persisted = $this->loadWorld();
        $hidden = array_filter($this->ofClass($persisted, Item::class), fn (Item $item) => $item->isHidden() === true);
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
