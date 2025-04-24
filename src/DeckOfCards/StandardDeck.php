<?php

namespace App\DeckOfCards;

use App\Card\Card;

/**
 * A standard French English deck of 52 playing cards.
 *
 * This class represents a standard deck of playing cards, which includes 52 cards
 * with 4 suits (hearts, diamonds, clubs, and spades) and 13 ranks (Ace to King).
 *
 * @author  jogm23
 */
class StandardDeck extends Deck
{
    private const array SUITS = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];

    private const array RANKS = [
        'A' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        '10' => 10,
        'J' => 11,
        'Q' => 12,
        'K' => 13,
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addCards();
    }

    /**
     * Add cards to the deck.
     *
     * This method initializes the deck with 52 standard playing cards.
     */
    protected function addCards(): void
    {
        foreach (self::SUITS as $suit) {
            foreach (self::RANKS as $rank => $value) {
                $this->cards[] = new Card($suit, (string) $rank, $value);
            }
        }
    }

    /**
     * Gets a copy of the deck, sorted on suit and rank
     *
     * @return Card[] The sorted deck of cards
     */
    public function getSorted(): array
    {
        $sorted = $this->cards;

        usort($sorted, function (Card $first, Card $second) {
            return $first->compareTo($second);
        });

        return $sorted;
    }
}
