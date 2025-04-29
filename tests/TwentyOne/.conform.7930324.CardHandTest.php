<?php

namespace App\Tests\TwentyOne;

use App\Card\Card;
use App\DeckOfCards\Deck;
use PHPUnit\Framework\TestCase;
use App\TwentyOne\CardHand;

class TestCardHand extends TestCase
{
    public function testAddCardToHand(): void
    {
        $cardMock = $this->createMock(Card::class);

        $cardHand = new CardHand();
        $cardHand->addCard($cardMock);

        $this->assertCount(1, $cardHand->getCards());
    }

    public function testGetCards(): void
    {
        $cardMock1 = $this->createMock(Card::class);
        $cardMock2 = $this->createMock(Card::class);

        $cardHand = new CardHand();
        $cardHand->addCard($cardMock1);
        $cardHand->addCard($cardMock2);

        $cards = $cardHand->getCards();

        $this->assertCount(2, $cards);
        $this->assertSame($cardMock1, $cards[0]);
        $this->assertSame($cardMock2, $cards[1]);
    }

    public function testJsonSerialize(): void
    {
        $cardMock1 = $this->createMock(Card::class);
        $cardMock2 = $this->createMock(Card::class);
        $cardMock1->method('jsonSerialize')
            ->willReturn(['mock' => 1]);
        $cardMock2->method('jsonSerialize')
            ->willReturn(['mock' => 2]);

        $cardHand = new CardHand();
        $cardHand->addCard($cardMock1);
        $cardHand->addCard($cardMock2);

        $expectedJson = json_encode([
            'cards' => [
                ['mock' => 1],
                ['mock' => 2]
            ],
        ]);
        $actualJson = json_encode($cardHand);

        $this->assertNotFalse($actualJson);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($actualJson);
        $this->assertJson($expectedJson);

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }
}
