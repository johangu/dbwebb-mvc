<?php

/**
* I'm not sure really how to test the abstract class.
* But hey, I atleast test the empty deck state
*
* Will dig deeper into fixtures later to see how I can do this
* with an abstract class.
*/

namespace App\Tests\DeckOfCards;

use App\Card\Card;
use PHPUnit\Framework\TestCase;
use App\DeckOfCards\Deck;

class DeckTest extends TestCase
{
    public function testGetCountNewEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $this->assertEquals(0, $deck->getCount());
    }

    public function testGetCardsNewEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $this->assertEquals([], $deck->getCards());
    }

    public function testDrawEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $this->assertNull($deck->draw());
    }

    public function testDrawHandEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $this->assertEquals([], $deck->drawHand(5));
    }

    public function testShuffleEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $deck->shuffle();
        $this->assertEquals([], $deck->getCards());
    }

    public function testJsonSerializeEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $expectedJson = json_encode([
            'type' => $deck::class,
            'cardCount' => 0,
            'cards' => [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($deck));
    }

    public function testToStringEmptyDeck(): void
    {
        $deck = $this->getMockForAbstractClass(Deck::class);
        $this->assertEquals('', (string)$deck);
    }
}
