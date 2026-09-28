<?php

namespace App\Tests\Adventure;

use App\Adventure\WorldLoader;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

/**
 * Helpers for testing the WorldLoader with mocked Doctrine.
 */
abstract class WorldLoaderTestCase extends TestCase
{
    /**
     * Load the world with mocked Doctrine and return what was persisted.
     *
     * @param array<class-string, array<object>> $existing Entities already in the database, by class
     * @param array<object> $removed Collects the removed entities
     *
     * @return array<object> The persisted entities
     */
    protected function loadWorld(array $existing = [], array &$removed = []): array
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
    protected function ofClass(array $entities, string $class): array
    {
        return array_values(array_filter($entities, fn (object $entity) => $entity instanceof $class));
    }
}
