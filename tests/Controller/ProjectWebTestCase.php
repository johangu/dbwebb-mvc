<?php

namespace App\Tests\Controller;

use App\Adventure\WorldLoader;
use App\Entity\Hotspot;
use App\Entity\Item;
use App\Entity\Passage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base for functional tests of the project, run against a test database.
 *
 * Before each test the schema is created from the entities and the
 * adventure is loaded, so every test starts with the same world.
 */
abstract class ProjectWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->entityManager = $entityManager;

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        /** @var WorldLoader $worldLoader */
        $worldLoader = static::getContainer()->get(WorldLoader::class);
        $worldLoader->load();
    }

    /**
     * Get the id of the hotspot for an item.
     */
    protected function hotspotFor(string $itemName): int
    {
        $item = $this->entityManager->getRepository(Item::class)->findOneBy(['name' => $itemName]);
        $hotspot = $this->entityManager->getRepository(Hotspot::class)->findOneBy(['item' => $item]);

        return (int) $hotspot?->getId();
    }

    /**
     * Get the id of an item.
     */
    protected function itemId(string $itemName): int
    {
        return (int) $this->entityManager->getRepository(Item::class)->findOneBy(['name' => $itemName])?->getId();
    }

    /**
     * Get the id of the passage in a direction from the room the player is in.
     */
    protected function passageFrom(string $roomName, string $direction): int
    {
        foreach ($this->entityManager->getRepository(Passage::class)->findBy(['direction' => $direction]) as $passage) {
            if ($passage->getFromRoom()?->getName() === $roomName) {
                return (int) $passage->getId();
            }
        }

        return 0;
    }

    /**
     * Start a game in the web interface.
     */
    protected function startGame(string $name = 'Testpirat'): void
    {
        $this->client->request('POST', '/proj/start', ['name' => $name]);
    }

    /**
     * Click on the hotspot of an item with a verb, and an item from the backpack when using.
     *
     * Follows the redirect like a browser does, so the message is shown before the next click.
     */
    protected function clickItem(string $itemName, string $verb, ?string $usedItemName = null): void
    {
        $this->client->request('POST', '/proj/click', array_filter([
            'hotspot' => $this->hotspotFor($itemName),
            'verb' => $verb,
            'item' => $usedItemName !== null ? $this->itemId($usedItemName) : null,
        ]));
        $this->client->followRedirect();
    }

    /**
     * Move through the passage in a direction from a room, following the redirect.
     */
    protected function move(string $roomName, string $direction): void
    {
        $this->client->request('POST', '/proj/move', ['passage' => $this->passageFrom($roomName, $direction)]);
        $this->client->followRedirect();
    }

    /**
     * Get the decoded JSON of the last response.
     *
     * @return array<mixed>
     */
    protected function json(): array
    {
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Get a list from the decoded JSON of the last response.
     *
     * @return array<mixed>
     */
    protected function jsonArray(string $key): array
    {
        $value = $this->json()[$key] ?? [];

        return is_array($value) ? $value : [];
    }
}
