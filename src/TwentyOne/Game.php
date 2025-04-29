<?php

namespace App\TwentyOne;

use App\Card\Card;
use App\DeckOfCards\Deck;
use App\DeckOfCards\StandardDeck;
use InvalidArgumentException;

/**
 * Class representing a game of 21.
 * This class manages the game state and the game logic.
 *
 * @author  jogm23
 */
class Game implements \JsonSerializable
{
    /** @var array<Player> */
    private array $players = [];

    /** @var Deck */
    private Deck $deck;

    /** @var int */
    private int $currentPlayerIndex = 1;

    /** @var string */
    private ?string $winner = null;

    /**
    * Constructor for the Game class.
    * Initializes the game with a standard deck of cards.
    */
    public function __construct(?Deck $deck = null)
    {
        $this->deck = $deck ?? new StandardDeck();
        $this->deck->shuffle();

        array_push($this->players, new Player('Bank'));
    }

    /**
     * Add a player to the game.
     *
     * @param Player $player The player to add
     *
     * @return void
     */
    public function addPlayer(Player $player): void
    {
        array_push($this->players, $player);
    }

    /**
     * Get the current player.
     *
     * @return Player The current player
     */
    public function getCurrentPlayer(): Player
    {
        return $this->players[$this->currentPlayerIndex];
    }

    /**
     * Get the winner of the game.
     *
     * @return string|null The name of the winner or null if there is no winner yet
     */
    public function getWinner(): ?string
    {
        return $this->winner;
    }

    /**
     * Get the players in the game.
     *
     * @return array<Player> The players in the game
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
    * Deal a card to the current player.
    * @param ?Player $player The player to deal to. Defaults to current active player.
    *
    * @return ?Card The drawn card
    */
    public function dealCard(?Player $player = null): ?Card
    {
        $player = $player ?? $this->getCurrentPlayer();

        $card = $this->deck->draw();
        if ($card === null) {
            return null;
        }

        $player->getHand()->addCard($card);

        return $card;
    }

    /**
     * Stand for the current player.
     * This method is called when the player decides to stop drawing cards.
     *
     * @return void
     */
    public function stand(): void
    {

        $this->currentPlayerIndex++;

        if ($this->currentPlayerIndex >= count($this->players)) {
            $this->currentPlayerIndex = 0;
            return;
        }

        $this->determineWinner();
    }

    /**
     * Play the bank's turn.
     * This method is called when the bank's turn starts.
     *
     * @return void
     */
    public function playBankTurn(): void
    {
        $bank = $this->players[0];

        while ($this->getScore($bank->getHand()) < 17) {
            $this->dealCard($bank);
        }
    }

    /**
     * Check the outcome of current player's hand.
     * Sets correct winner if the player has 21 or is bust.
     *
     * @return void
     */
    public function checkOutcome(): void
    {
        $player = $this->getCurrentPlayer();
        $score = $this->getScore($player->getHand());

        if ($score > 21) {
            $this->winner = 'Bank';
        }

        if ($score === 21) {
            $this->winner = $player->getName();
        }
    }

    /**
     * Determine the winner of the game.
     * This method checks the scores of all players and determines the winner.
     *
     * @return void
     */
    public function determineWinner(): void
    {
        // If the player is bust, we want to ensure that the banks
        // empty hand with 0 points will be the winner
        $highestScore = -1;
        $winner = null;

        foreach ($this->players as $player) {
            $score = $this->getScore($player->getHand());
            if ($score > 21) {
                continue;
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $winner = $player->getName();
            }
        }

        $this->winner = $winner;
    }

    /**
     * Get the score for a given CardHand.
     *
     * @param CardHand $hand The hand to score
     *
     * @return int The score of the hand
     */
    public function getScore(CardHand $hand): int
    {
        $aces = array_filter($hand->getCards(), function ($card) {
            return $card->isAce();
        });
        $faceCards = array_filter($hand->getCards(), function ($card) {
            return $card->isFaceCard();
        });
        $numberedCards = array_filter($hand->getCards(), function ($card) {
            return !$card->isAce() && !$card->isFaceCard();
        });

        $score = 0;
        foreach ($numberedCards as $card) {
            $score += $card->getValue();
        }

        // Face cards are worth 10
        $score += count($faceCards) * 10;

        // In 21 aces are worth 1 or 11, let's handle that by counting as 11 as long
        // as it doesn't bust the hand
        foreach ($aces as $card) {
            $score += ($score + 11 > 21) ? 1 : 11;
        }


        return $score;
    }

    /**
     * String representation of the game state.
     * Pretty prints the game state for debugging purposes.
     *
     * @return string The string representation of the game state
     */
    public function __toString(): string
    {
        $output = "Game State:\n";
        $output .= "Deck: " . json_encode($this->deck, JSON_PRETTY_PRINT) . "\n";
        $output .= "Players:\n";

        foreach ($this->players as $name => $player) {
            $output .= "$name: " . json_encode($player, JSON_PRETTY_PRINT) . "\n";
        }

        return $output;
    }

    /**
    * Get the JSON representation of the game.
    *
    * @return array<string, mixed> The JSON representation of the game
    */
    public function jsonSerialize(): array
    {
        return [
            'players' => $this->players,
            'deck' => $this->deck,
        ];
    }
}
