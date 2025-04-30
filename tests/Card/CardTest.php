<?php

namespace App\Tests\Card;

use PHPUnit\Framework\TestCase;
use App\Card\Card;

class CardTest extends TestCase
{
    public function testCreateCard(): void
    {
        $card = new Card('Hearts', 'A', 1);
        $this->assertEquals('A', $card->getRank());
        $this->assertEquals('Hearts', $card->getSuit());
        $this->assertEquals(1, $card->getValue());
        $this->assertTrue($card->isAce());
        $this->assertFalse($card->isFaceCard());
        $this->assertFalse($card->isJoker());
    }

    public function testCardToString(): void
    {
        $card = new Card('Diamonds', 'K', 13);
        $this->assertEquals('K of Diamonds', (string)$card);
    }

    public function testJokerCardToString(): void
    {
        $card = new Card('Joker', 'Joker', 0);
        $this->assertEquals('Joker', (string)$card);
    }

    public function testCompareCards(): void
    {
        $card1 = new Card('Clubs', '10', 10);
        $card2 = new Card('Spades', 'J', 11);

        $this->assertEquals(-1, $card1->compareTo($card2));
        $this->assertEquals(1, $card2->compareTo($card1));
    }

    public function testCompareEqualCards(): void
    {
        $card1 = new Card('Hearts', 'Q', 12);
        $card2 = new Card('Hearts', 'Q', 12);

        $this->assertEquals(0, $card1->compareTo($card2));
    }

    public function testJsonSerialize(): void
    {
        $card = new Card('Spades', 'A', 1);

        $json = json_encode($card);
        $expectedJson = json_encode([
            'suit' => 'Spades',
            'rank' => 'A',
            'value' => 1,
            'isFaceCard' => false,
            'isJoker' => false,
            'isAce' => true,
        ]);

        $this->assertNotFalse($json);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($json);
        $this->assertJson($expectedJson);
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
