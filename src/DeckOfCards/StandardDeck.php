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
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $ranks = [
            1 => 'A',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            7 => '7',
            8 => '8',
            9 => '9',
            10 => '10',
            11 => 'J',
            12 => 'Q',
            13 => 'K',
        ];

        foreach ($suits as $suit) {
            foreach ($ranks as $value => $rank) {
                $this->cards[] = new Card(
                    $suit,
                    $rank,
                    $value,
                    in_array($rank, ['J', 'Q', 'K']),
                    $rank === 'A',
                    false
                );
            }
        }
    }

    /**
     * Gets a copy of the deck, sorted on suit and rank
     *
     * @return array The sorted deck of cards
     */
    public function getSorted(): array
    {
        $sorted = $this->cards;

        usort($sorted, function (Card $a, Card $b) {
            return $a->compareTo($b);
        });

        return $sorted;
    }
}
