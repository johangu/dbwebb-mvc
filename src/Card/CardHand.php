<?php

namespace App\Card;

use App\DeckOfCards\Deck;

/**
 * A hand of playing cards.
 *
 * This class represents a hand of playing cards
 *
 * @author  jogm23
 */
class CardHand
{
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
            throw new \InvalidArgumentException('Not enough cards in the deck');
        }

        $this->cards = $deck->drawHand($count);
    }

    /**
     * Get the cards in the hand.
     *
     * @return Card[] The cards in the hand
     */
    public function getCards(): array
    {
        return $this->cards;
    }
}
