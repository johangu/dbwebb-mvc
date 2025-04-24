<?php

namespace App\Card;

use App\DeckOfCards\Deck;
use InvalidArgumentException;

/**
 * A hand of playing cards.
 *
 * This class represents a hand of playing cards
 *
 * @author  jogm23
 */
class CardHand implements \JsonSerializable
{
    /** @var Card[] */
    private array $cards = [];

    /**
     * Constructor
     *
     * @param  Deck  $deck  The deck to draw from
     * @param  int  $count  How many cards to draw
     */
    public function __construct(Deck $deck, int $count = 5)
    {
        if ($deck->getCount() < $count) {
            throw new InvalidArgumentException('Not enough cards in the deck');
        }

        $this->cards = $deck->drawHand($count);
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
     * @return array<string, mixed> The JSON representation of the card
     */
    public function jsonSerialize(): array
    {
        return [
            'cards' => $this->cards,
            'count' => count($this->cards),
        ];
    }
}
