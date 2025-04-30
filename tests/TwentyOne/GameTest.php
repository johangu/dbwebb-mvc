<?php

namespace App\Tests\TwentyOne;

use App\Card\Card;
use App\TwentyOne\CardHand;
use PHPUnit\Framework\TestCase;
use App\DeckOfCards\Deck;
use App\TwentyOne\Game;
use App\TwentyOne\Player;
use ReflectionClass;

class GameTest extends TestCase
{
    public function testAddPlayer(): void
    {
        $playerMock = $this->createMock(Player::class);
        $playerMock->method('getName')->willReturn('Test Testsson');
        $game = new Game();
        $game->addPlayer($playerMock);

        $this->assertCount(2, $game->getPlayers());
        $this->assertEquals('Test Testsson', $game->getPlayers()[1]->getName());
    }

    public function testGetCurrentPlayer(): void
    {
        $game = new Game();
        $playerMock = $this->createMock(Player::class);
        $playerMock->method('getName')->willReturn('Test Testsson');
        $game->addPlayer($playerMock);

        $this->assertEquals('Test Testsson', $game->getCurrentPlayer()->getName());
    }

    public function testCheckScoreFaceCard(): void
    {
        $game = new Game();
        $cardMock = $this->createMock(Card::class);
        $cardMock->method('isFaceCard')->willReturn(true);
        $cardHandMock = $this->createMock(CardHand::class);
        $cardHandMock->method('getCards')
            ->willReturn([$cardMock]);

        $this->assertEquals(10, $game->getScore($cardHandMock));
    }

    public function testCheckScoreAceNotBust(): void
    {
        $game = new Game();
        $cardMock = $this->createMock(Card::class);
        $cardMock->method('isAce')->willReturn(true);
        $cardHandMock = $this->createMock(CardHand::class);
        $cardHandMock->method('getCards')
            ->willReturn([$cardMock]);

        $this->assertEquals(11, $game->getScore($cardHandMock));
    }

    public function testCheckScoreAceBust(): void
    {
        $game = new Game();
        $cardMock = $this->createMock(Card::class);
        $cardMock->method('isAce')->willReturn(true);
        $cardHandMock = $this->createMock(CardHand::class);
        $cardHandMock->method('getCards')
            ->willReturn([$cardMock, $cardMock, $cardMock, $cardMock]);

        $this->assertEquals(14, $game->getScore($cardHandMock));
    }

    public function testCheckScoreNumberedCard(): void
    {
        $game = new Game();
        $cardMock = $this->createMock(Card::class);
        $cardMock->method('isFaceCard')->willReturn(false);
        $cardMock->method('getValue')->willReturn(5);
        $cardHandMock = $this->createMock(CardHand::class);
        $cardHandMock->method('getCards')
            ->willReturn([$cardMock]);

        $this->assertEquals(5, $game->getScore($cardHandMock));
    }

    public function testGetWinnerNoWinner(): void
    {
        $game = new Game();

        $this->assertNull($game->getWinner());
    }

    public function testDealCard(): void
    {
        $cardMock = $this->createMock(Card::class);
        $deckMock = $this->createMock(Deck::class);
        $deckMock->method('draw')->willReturn($cardMock);
        $playerMock = $this->createMock(Player::class);

        $game = new Game($deckMock);
        $game->addPlayer($playerMock);
        $card = $game->dealCard($playerMock);

        $this->assertEquals($cardMock, $card);
    }

    public function testDealCardEmptyDeck(): void
    {
        $deckMock = $this->createMock(Deck::class);
        $deckMock->method('draw')->willReturn(null);
        $playerMock = $this->createMock(Player::class);

        $game = new Game($deckMock);
        $game->addPlayer($playerMock);
        $card = $game->dealCard($playerMock);

        $this->assertNull($card);
    }

    public function testPlayBankTurnDrawsUntilAtLeast17(): void
    {
        $dealtCards = [];

        $cardMock = $this->createConfiguredMock(Card::class, [
            'getValue' => 6,
            'isFaceCard' => false,
            'isAce' => false,
        ]);

        $deckMock = $this->createMock(Deck::class);
        $deckMock->expects($this->exactly(3))
            ->method('draw')
            ->willReturnOnConsecutiveCalls($cardMock, $cardMock, $cardMock);

        $handMock = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards', 'addCard'])
            ->getMock();

        $handMock->method('getCards')
            ->willReturnCallback(function () use (&$dealtCards) {
                return $dealtCards;
            });

        $handMock->method('addCard')
            ->willReturnCallback(function (Card $card) use (&$dealtCards) {
                $dealtCards[] = $card;
            });

        $bankMock = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand'])
            ->getMock();
        $bankMock->method('getHand')->willReturn($handMock);

        $game = new Game($deckMock);

        $prop = (new ReflectionClass($game))->getProperty('players');
        $prop->setAccessible(true);
        $prop->setValue($game, [$bankMock]);

        $game->playBankTurn();

        $this->assertCount(3, $dealtCards);
    }

    public function testCheckOutcomeBust(): void
    {
        // Let's create this fictional card for the sake of the test
        $cardMock = $this->createConfiguredMock(Card::class, [
            'getValue' => 22,
            'isFaceCard' => false,
            'isAce' => false,
        ]);
        $handMock = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards'])
            ->getMock();
        $playerMock = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand', 'getName'])
            ->getMock();

        $handMock->method('getCards')
            ->willReturn([$cardMock]);

        $playerMock->method('getHand')->willReturn($handMock);
        $playerMock->method('getName')->willReturn('Test Testsson');

        $game = new Game();
        $game->addPlayer($playerMock);

        $game->checkOutcome();
        $this->assertEquals('Bank', $game->getWinner());
    }

    public function testCheckOutcome21(): void
    {
        // Let's create this fictional card for the sake of the test
        $cardMock = $this->createConfiguredMock(Card::class, [
            'getValue' => 21,
            'isFaceCard' => false,
            'isAce' => false,
        ]);
        $handMock = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards'])
            ->getMock();
        $playerMock = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand', 'getName'])
            ->getMock();

        $handMock->method('getCards')
            ->willReturn([$cardMock]);

        $playerMock->method('getHand')->willReturn($handMock);
        $playerMock->method('getName')->willReturn('Test Testsson');

        $game = new Game();
        $game->addPlayer($playerMock);

        $game->checkOutcome();
        $this->assertEquals('Test Testsson', $game->getWinner());
    }

    public function testCheckOutcomeNoAction(): void
    {
        $cardMock = $this->createConfiguredMock(Card::class, [
            'getValue' => 10,
            'isFaceCard' => false,
            'isAce' => false,
        ]);
        $handMock = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards'])
            ->getMock();
        $playerMock = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand', 'getName'])
            ->getMock();

        $handMock->method('getCards')
            ->willReturn([$cardMock]);

        $playerMock->method('getHand')->willReturn($handMock);
        $playerMock->method('getName')->willReturn('Test Testsson');

        $game = new Game();
        $game->addPlayer($playerMock);

        $game->checkOutcome();
        $this->assertNull($game->getWinner());
    }

    public function testJsonSerialize(): void
    {
        $mockDeck = $this->createMock(Deck::class);
        $playerMock = $this->createMock(Player::class);
        $playerMock->method('getName')->willReturn('Test Testsson');

        $game = new Game($mockDeck);
        $game->addPlayer($playerMock);

        $json = json_encode($game);
        $expectedJson =
            json_encode([
                'players' => [
                    // bank is instantiated in Game so that one actually serializes here
                    // maybe a candidate for refactoring
                    ['name' => 'Bank', 'hand' => ['cards' => []]],
                    []
                ],
                'deck' => [],
            ]);
        $this->assertNotFalse($json);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($json);
        $this->assertJson($expectedJson);
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }

    public function testToString(): void
    {
        $mockDeck = $this->createMock(Deck::class);
        $playerMock = $this->createMock(Player::class);
        $playerMock->method('getName')->willReturn('Test Testsson');

        $game = new Game($mockDeck);
        $game->addPlayer($playerMock);

        $string = (string)$game;
        $this->assertStringContainsString('Game State:', $string);
        $this->assertStringContainsString('Deck:', $string);
        $this->assertStringContainsString('Players:', $string);
    }

    public function testDetermineWinnerPlayerBust(): void
    {
        $mockPlayer = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand'])
            ->getMock();
        $mockHand = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards'])
            ->getMock();
        $mockCard = $this->getMockBuilder(Card::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'isFaceCard', 'isAce'])
            ->getMock();

        $mockPlayer->method('getHand')->willReturn($mockHand);
        $mockHand->method('getCards')->willReturn([$mockCard]);
        $mockCard->method('getValue')->willReturn(22);
        $mockCard->method('isFaceCard')->willReturn(false);
        $mockCard->method('isAce')->willReturn(false);

        $game = new Game();
        $game->addPlayer($mockPlayer);

        $game->determineWinner();

        $this->assertEquals('Bank', $game->getWinner());
    }

    public function testDetermineWinnerPlayer21(): void
    {
        $mockPlayer = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand', 'getName'])
            ->getMock();
        $mockHand = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards'])
            ->getMock();
        $mockCard = $this->getMockBuilder(Card::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'isFaceCard', 'isAce'])
            ->getMock();

        $mockPlayer->method('getHand')->willReturn($mockHand);
        $mockPlayer->method('getName')->willReturn('Test Testsson');
        $mockHand->method('getCards')->willReturn([$mockCard]);
        $mockCard->method('getValue')->willReturn(21);
        $mockCard->method('isFaceCard')->willReturn(false);
        $mockCard->method('isAce')->willReturn(false);

        $game = new Game();
        $game->addPlayer($mockPlayer);

        $game->determineWinner();

        $this->assertEquals('Test Testsson', $game->getWinner());
    }

    public function testDetermineWinnerHighScore(): void
    {
        $mockPlayer = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHand', 'getName'])
            ->getMock();
        $mockHand = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCards'])
            ->getMock();
        $mockCard = $this->getMockBuilder(Card::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isFaceCard', 'isAce'])
            ->getMock();

        $mockPlayer->method('getHand')->willReturn($mockHand);
        $mockPlayer->method('getName')->willReturn('Test Testsson');
        $mockHand->method('getCards')->willReturn([$mockCard]);
        $mockCard->method('isFaceCard')->willReturn(true);
        $mockCard->method('isAce')->willReturn(false);

        $game = new Game();
        $game->addPlayer($mockPlayer);

        $game->determineWinner();

        $this->assertEquals('Test Testsson', $game->getWinner());
    }
}
