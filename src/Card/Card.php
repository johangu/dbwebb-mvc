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
    private string $rank;

    private string $suit;

    private int $value;

    private bool $isFaceCard;

    private bool $isAce;

    private bool $isJoker;

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
     * Get the suit of the card
     *
     * @return string The suit of the card
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Get the rank of the card
     *
     * @return string The rank of the card
     */
    public function getRank(): string
    {
        return $this->rank;
    }

    /**
     * Get the value of the card
     *
     * @return int The value of the card
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Check if the card is a face card
     *
     * @return bool True if the card is a face card, false otherwise
     */
    public function isFaceCard(): bool
    {
        return $this->isFaceCard;
    }

    /**
     * Check if the card is an Ace
     *
     * @return bool True if the card is an Ace, false otherwise
     */
    public function isAce(): bool
    {
        return $this->isAce;
    }

    /**
     * Check if the card is a Joker
     *
     * @return bool True if the card is a Joker, false otherwise
     */
    public function isJoker(): bool
    {
        return $this->isJoker;
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
