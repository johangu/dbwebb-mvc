<?php

namespace App\DeckOfCards;

use App\Card\Card;

/**
 * An abstract deck of playing cards.
 * Can be used to represent a standard 52-card deck or any other custom deck.
 *
 * @author  jogm23
 */
abstract class Deck implements \JsonSerializable
{
    protected $cards = [];

    abstract protected function addCards(): void;

    abstract public function getSorted(): array;

    /**
     * Get the cards in the deck.
     *
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Get the number of cards in the deck.
     *
     * @return int The number of cards in the deck
     */
    public function getCount(): int
    {
        return count($this->cards);
    }

    /**
     * Shuffle the deck.
     */
    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * Draw a card from the deck.
     *
     * @param  int  $idx  The index of the card to draw (0-based)
     * @return Card|null The drawn card, or null if the index is out of bounds
     */
    public function draw(int $idx = 0): ?Card
    {
        if ($idx < 0 || $idx >= count($this->cards)) {
            return null;
        }

        return array_splice($this->cards, $idx, 1)[0];
    }

    /**
     * Draw a hand of cards from the deck.
     *
     * @param  int  $count  The number of cards to draw
     * @return Card[]
     */
    public function drawHand(int $count): array
    {
        if ($count < 0 || $count > count($this->cards)) {
            return [];
        }

        return array_splice($this->cards, 0, $count);
    }

    /**
     * Get the JSON representation of the deck.
     *
     * @return array The JSON representation of the deck
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => (new \ReflectionClass($this))->getShortName(),
            'cardCount' => $this->getCount(),
            'cards' => $this->getCards(),
        ];
    }

    /**
     * Print all cards in the deck
     */
    public function __toString(): string
    {
        $output = '';
        foreach ($this->cards as $card) {
            $output .= (string) $card.' ';
        }

        return trim($output);
    }
}
