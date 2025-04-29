<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\DeckOfCards\Deck;
use PHPUnit\Framework\TestCase;
use App\Card\CardHand;

class TestCardHand extends TestCase
{
    public function testCreateCardHand(): void
    {
        $cardMock = $this->createMock(Card::class);
        $cardMock->method('jsonSerialize')
            ->willReturn(['mock' => true]);
        $deckMock = $this->createMock(Deck::class);
        $deckMock->method('drawHand')
            ->willReturn([$cardMock, $cardMock, $cardMock, $cardMock, $cardMock]);
        $deckMock->method('getCount')
            ->willReturn(52);

        $cardHand = new CardHand($deckMock, 5);
        $this->assertInstanceOf(CardHand::class, $cardHand);
        $this->assertCount(5, $cardHand->getCards());
    }

    public function testCreateHandWithTooSmallDeck(): void
    {
        $deckMock = $this->createMock(Deck::class);
        $deckMock->method('getCount')
            ->willReturn(3);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not enough cards in the deck');

        new CardHand($deckMock, 5);
    }

    public function testJsonSerialize(): void
    {
        $cardMock = $this->createMock(Card::class);
        $cardMock->method('jsonSerialize')
            ->willReturn(['mock' => true]);

        $deckMock = $this->createMock(Deck::class);
        $deckMock->method('drawHand')
            ->willReturn([$cardMock]);
        $deckMock->method('getCount')
            ->willReturn(52);

        $cardHand = new CardHand($deckMock, 1);
        $expectedJson = json_encode([
            'cards' => [
                ['mock' => true],
            ],
            'count' => 1,
        ]);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            json_encode($cardHand)
        );
    }
}
