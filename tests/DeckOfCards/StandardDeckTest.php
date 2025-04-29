<?php

namespace App\Tests\DeckOfCards;

use App\Card\Card;
use PHPUnit\Framework\TestCase;
use App\DeckOfCards\StandardDeck;

class StandardDeckTest extends TestCase
{
    public function testGetCount(): void
    {
        $deck = new StandardDeck();
        $this->assertEquals(52, $deck->getCount());
    }

    public function testGetCards(): void
    {
        $deck = new StandardDeck();
        $cards = $deck->getCards();

        $this->assertCount(52, $cards);
        foreach ($cards as $card) {
            $this->assertInstanceOf(Card::class, $card);
        }
    }

    public function testShuffle(): void
    {
        $deck = new StandardDeck();
        $unshuffled = $deck->getCards();
        $deck->shuffle();
        $shuffled = $deck->getCards();

        $this->assertNotEquals($unshuffled, $shuffled);
    }

    public function testGetSorted(): void
    {
        $deck = new StandardDeck();
        $deck->shuffle();
        $sorted = $deck->getSorted();

        $this->assertNotSame($deck->getCards(), $sorted);
        $this->assertCount(52, $sorted);
        // Should be sorted by suit and rank
        // Assuming the suits are ordered as Clubs, Diamonds, Hearts, Spades
        $suits = ['Clubs', 'Diamonds', 'Hearts', 'Spades'];

        for ($deckIndex = 0; $deckIndex < 52; $deckIndex++) {
            $card = $sorted[$deckIndex];
            $expectedSuit = $suits[floor($deckIndex / 13)];
            $expectedValue = $deckIndex % 13 + 1; // +1 because values are 1-13

            $this->assertEquals($expectedSuit, $card->getSuit());
            $this->assertEquals($expectedValue, $card->getValue());
        }
    }
}
