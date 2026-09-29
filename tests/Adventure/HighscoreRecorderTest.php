<?php

namespace App\Tests\Adventure;

use App\Adventure\GameSession;
use App\Adventure\HighscoreRecorder;
use App\Entity\Highscore;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HighscoreRecorderTest extends TestCase
{
    /**
     * Create a game session that has been won or not.
     */
    private function createGame(bool $won): GameSession&MockObject
    {
        return $this->createConfiguredMock(GameSession::class, [
            'hasWon' => $won,
            'getPlayerName' => 'Test Testsson',
            'getMoves' => 13,
        ]);
    }

    public function testRecordWonGame(): void
    {
        $saved = [];
        $managerMock = $this->createMock(ObjectManager::class);
        $managerMock->method('persist')
            ->willReturnCallback(function (object $entity) use (&$saved) {
                $saved[] = $entity;
            });
        $managerMock->expects($this->once())->method('flush');
        $doctrineMock = $this->createConfiguredMock(ManagerRegistry::class, ['getManager' => $managerMock]);

        $gameMock = $this->createGame(true);
        $gameMock->expects($this->once())->method('markRecorded');

        $this->assertTrue((new HighscoreRecorder($doctrineMock))->record($gameMock));
        $this->assertCount(1, $saved);
        $this->assertInstanceOf(Highscore::class, $saved[0]);
        $this->assertEquals('Test Testsson', $saved[0]->getName());
        $this->assertEquals(13, $saved[0]->getMoves());
    }

    public function testDoNotRecordGameThatIsNotWon(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);
        $managerMock->expects($this->never())->method('persist');
        $doctrineMock = $this->createConfiguredMock(ManagerRegistry::class, ['getManager' => $managerMock]);

        $this->assertFalse((new HighscoreRecorder($doctrineMock))->record($this->createGame(false)));
    }

    public function testDoNotRecordGameTwice(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);
        $managerMock->expects($this->never())->method('persist');
        $doctrineMock = $this->createConfiguredMock(ManagerRegistry::class, ['getManager' => $managerMock]);

        $gameMock = $this->createConfiguredMock(GameSession::class, ['hasWon' => true, 'isRecorded' => true]);

        $this->assertFalse((new HighscoreRecorder($doctrineMock))->record($gameMock));
    }
}
