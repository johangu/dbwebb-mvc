<?php

namespace App\TwentyOne;

use App\Card\Card;

/**
 * Class representing a hand of cards in the game of 21.
 *
 * @author  jogm23
 */
class CardHand implements \JsonSerializable
{
    /** @var array<Card> */
    private array $cards = [];

    /**
     * Add a card to the hand.
     *
     * @param Card $card The card to add
     */
    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Get the cards in the hand.
     *
     * @return array<Card> The cards in the hand
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
    * Get the JSON representation of the hand.
    *
    * @return array<string, mixed> The JSON representation of the hand
    */
    public function jsonSerialize(): array
    {
        return [
            'cards' => $this->cards,
        ];
    }
}
