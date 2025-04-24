<?php

namespace App\Card;

/**
 * A playing card.
 *
 * This class represents a playing card, which has a rank, suit, and value.
 *
 * @author  jogm23
 */
class Card implements \JsonSerializable
{
    public string $rank;

    public string $suit;

    public int $value;

    public bool $isFaceCard;

    public bool $isAce;

    public bool $isJoker;

    /**
     * Constructor
     *
     * @param  string  $suit  The suit of the card
     * @param  string  $rank  The rank of the card
     * @param  int  $value  The value of the card
     * @param  bool  $isFaceCard  Whether the card is a face card (Jack, Queen, King)
     * @param  bool  $isAce  Whether the card is an Ace
     * @param  bool  $isJoker  Whether the card is a Joker
     */
    public function __construct(
        string $suit,
        string $rank,
        int $value = 0,
        bool $isFaceCard = false,
        bool $isAce = false,
        bool $isJoker = false
    ) {
        $this->suit = $suit;
        $this->rank = $rank;
        $this->value = $value;
        $this->isFaceCard = $isFaceCard;
        $this->isAce = $isAce;
        $this->isJoker = $isJoker;
    }

    /**
     * Compare the card to another card
     *
     * @param  Card  $other  The card to compare to
     * @return int -1 if this card is less than the other card, 0 if they are equal,
     *             1 if this card is greater than the other card
     */
    public function compareTo(Card $other): int
    {
        return [$this->suit, $this->value] <=> [$other->suit, $other->value];
    }

    /**
     * Get the JSON representation of the card
     *
     * @return array The JSON representation of the card
     */
    public function jsonSerialize(): array
    {
        return [
            'suit' => $this->suit,
            'rank' => $this->rank,
            'value' => $this->value,
            'isFaceCard' => $this->isFaceCard,
            'isAce' => $this->isAce,
            'isJoker' => $this->isJoker,
        ];
    }

    /**
     * Print a string representation of the card
     */
    public function __toString(): string
    {
        $symbol = match ($this->suit) {
            'Hearts' => '♥',
            'Diamonds' => '♦',
            'Clubs' => '♣',
            'Spades' => '♠',
            default => '?'
        };

        if ($this->isJoker) {
            return 'Joker';
        }

        return "[{$this->rank} {$symbol}]";
    }
}
