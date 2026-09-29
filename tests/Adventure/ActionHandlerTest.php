<?php

namespace App\Tests\Adventure;

use App\Adventure\ActionHandler;
use App\Adventure\GameSession;
use App\Adventure\HighscoreRecorder;
use App\Entity\Hotspot;
use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use App\Repository\InteractionRepository;
use PHPUnit\Framework\TestCase;

class ActionHandlerTest extends TestCase
{
    private Room $room;

    private GameSession $game;

    protected function setUp(): void
    {
        $this->room = $this->createConfiguredMock(Room::class, ['getId' => 1]);
        $this->game = new GameSession('Test Testsson', $this->room);
    }

    /**
     * Create an action handler whose repository returns the given interactions.
     *
     * @param array<Interaction> $interactions
     */
    private function createHandler(array $interactions = []): ActionHandler
    {
        $repositoryMock = $this->createMock(InteractionRepository::class);
        $repositoryMock->method('findBy')->willReturn($interactions);

        return new ActionHandler($repositoryMock, $this->createMock(HighscoreRecorder::class));
    }

    /**
     * Create an item in the room with id 1.
     *
     * @param array<string, mixed> $config Methods to override on the item mock
     */
    private function createItem(int $itemId, string $name, array $config = []): Item
    {
        return $this->createConfiguredMock(Item::class, array_merge([
            'getId' => $itemId,
            'getName' => $name,
            'getDescription' => "Beskrivning av $name.",
            'getRoom' => $this->room,
            'isStartsHidden' => false,
            'isPickable' => false,
        ], $config));
    }

    public function testClickOnPassageMoves(): void
    {
        $passageMock = $this->createConfiguredMock(Passage::class, [
            'getFromRoom' => $this->room,
            'getToRoom' => $this->createConfiguredMock(Room::class, ['getId' => 2]),
            'isStartsLocked' => false,
        ]);
        $hotspotMock = $this->createConfiguredMock(Hotspot::class, ['getPassage' => $passageMock]);

        $this->assertEquals('', $this->createHandler()->click($this->game, $hotspotMock, 'take'));
        $this->assertEquals(2, $this->game->getCurrentRoomId());
    }

    public function testMoveThroughLockedPassage(): void
    {
        $passageMock = $this->createConfiguredMock(Passage::class, ['getFromRoom' => $this->room, 'isStartsLocked' => true]);

        $this->assertEquals('Det går inte att ta sig dit än.', $this->createHandler()->move($this->game, $passageMock));
    }

    public function testClickOnHotspotWithoutItemOrPassage(): void
    {
        $hotspotMock = $this->createMock(Hotspot::class);

        $this->assertEquals('', $this->createHandler()->click($this->game, $hotspotMock, 'examine'));
    }

    public function testExamineShowsDescription(): void
    {
        $hotspotMock = $this->createConfiguredMock(Hotspot::class, ['getItem' => $this->createItem(3, 'lådorna')]);

        $this->assertEquals('Beskrivning av lådorna.', $this->createHandler()->click($this->game, $hotspotMock, 'examine'));
    }

    public function testExamineShowsInteractionMessage(): void
    {
        $barrel = $this->createItem(3, 'tunnan');
        $interactionMock = $this->createConfiguredMock(Interaction::class, [
            'getId' => 20,
            'getRoom' => $this->room,
            'getTarget' => $barrel,
            'getMessage' => 'Du hittar något.',
        ]);

        $this->assertEquals('Du hittar något.', $this->createHandler([$interactionMock])->act($this->game, 'examine', $barrel));
    }

    public function testUnknownVerbExaminesAndRecordsGame(): void
    {
        $repositoryMock = $this->createConfiguredMock(InteractionRepository::class, ['findBy' => []]);
        $recorderMock = $this->createMock(HighscoreRecorder::class);
        $recorderMock->expects($this->once())->method('record')->with($this->game);
        $handler = new ActionHandler($repositoryMock, $recorderMock);

        $this->assertEquals('Beskrivning av nätet.', $handler->act($this->game, 'dance', $this->createItem(3, 'nätet')));
    }

    public function testTake(): void
    {
        $handler = $this->createHandler();

        $this->assertEquals('Du tar pungen.', $handler->act($this->game, 'take', $this->createItem(4, 'pungen', ['isPickable' => true])));
        $this->assertEquals('Det går inte att ta tunnan.', $handler->act($this->game, 'take', $this->createItem(3, 'tunnan')));
    }

    public function testNothingHappensAfterWinning(): void
    {
        $gameMock = $this->createConfiguredMock(GameSession::class, ['hasWon' => true]);
        $gameMock->expects($this->never())->method('examine');
        $gameMock->expects($this->never())->method('move');
        $handler = $this->createHandler();

        $this->assertEquals('Du har redan hittat skatten.', $handler->act($gameMock, 'examine', $this->createItem(3, 'tunnan')));
        $this->assertEquals('Du har redan hittat skatten.', $handler->move($gameMock, $this->createMock(Passage::class)));
    }

    public function testUseWithoutSelectedItem(): void
    {
        $this->assertEquals(
            'Välj först något i ryggsäcken att använda.',
            $this->createHandler()->act($this->game, 'use', $this->createItem(5, 'sjömännen'))
        );
    }

    public function testUseWithoutMatchingInteraction(): void
    {
        $purse = $this->createItem(4, 'pungen', ['isPickable' => true]);
        $this->game->take($purse);

        $this->assertEquals('Inget händer.', $this->createHandler()->act($this->game, 'use', $this->createItem(3, 'tunnan'), $purse));
    }
}
