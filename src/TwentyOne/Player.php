<?php

namespace App\TwentyOne;

/**
 * Class representing a player in the game of 21.
 *
 * @author  jogm23
 */
class Player implements \JsonSerializable
{
    /** @var string */
    private string $name;

    /** @var CardHand */
    private CardHand $hand;

    /**
    * Constructor for the Player class.
    * Initializes the player with a name and an empty hand.
    */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->hand = new CardHand();
    }

    /**
    * Get the player's name.
    *
    * @return string The player's name
    */
    public function getName(): string
    {
        return $this->name;
    }

    /**
    * Get the player's hand.
    *
    * @return CardHand The player's hand
    */
    public function getHand(): CardHand
    {
        return $this->hand;
    }

    /**
    * Get the JSON representation of the player.
    *
    * @return array<string, mixed> The JSON representation of the player
    */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'hand' => $this->hand,
        ];
    }
}
