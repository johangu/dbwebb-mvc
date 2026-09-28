<?php

namespace App\Adventure;

use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;

/**
 * The state of one player's adventure.
 *
 * The world in the database never changes during play, everything that happens
 * to the player is recorded here instead. The game session is stored in the
 * session, so it only keeps ids and never the entities themselves.
 *
 * @author  jogm23
 */
class GameSession implements \JsonSerializable
{
    private string $playerName;

    private int $currentRoomId;

    private Backpack $backpack;

    /** @var array<int> */
    private array $revealedItemIds = [];

    /** @var array<int> */
    private array $unlockedPassageIds = [];

    private int $moves = 0;

    /**
     * Constructor
     *
     * @param string $playerName The name of the player
     * @param Room $startRoom The room where the adventure starts
     */
    public function __construct(string $playerName, Room $startRoom)
    {
        $this->playerName = $playerName;
        $this->currentRoomId = (int) $startRoom->getId();
        $this->backpack = new Backpack();
    }

    /**
     * Get the name of the player.
     *
     * @return string The name of the player
     */
    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    /**
     * Get the id of the room the player is in.
     *
     * @return int The id of the current room
     */
    public function getCurrentRoomId(): int
    {
        return $this->currentRoomId;
    }

    /**
     * Get the player's backpack.
     *
     * @return Backpack The backpack
     */
    public function getBackpack(): Backpack
    {
        return $this->backpack;
    }

    /**
     * Get the number of moves the player has made.
     *
     * @return int The number of moves
     */
    public function getMoves(): int
    {
        return $this->moves;
    }

    /**
     * Check if an item can be seen by the player.
     *
     * An item is visible if it doesn't start hidden or if the player has
     * revealed it, as long as it hasn't been put in the backpack.
     *
     * @param Item $item The item to check
     *
     * @return bool True if the item is visible, false otherwise
     */
    public function isItemVisible(Item $item): bool
    {
        if ($this->backpack->has($item)) {
            return false;
        }

        return !$item->isStartsHidden() || in_array($item->getId(), $this->revealedItemIds, true);
    }

    /**
     * Check if a passage is open for the player.
     *
     * @param Passage $passage The passage to check
     *
     * @return bool True if the passage can be used, false otherwise
     */
    public function isPassageOpen(Passage $passage): bool
    {
        return !$passage->isStartsLocked() || in_array($passage->getId(), $this->unlockedPassageIds, true);
    }

    /**
     * Move the player through a passage.
     *
     * Every attempt counts as a move, even if the passage can't be used.
     *
     * @param Passage $passage The passage to move through
     *
     * @return bool True if the player moved, false if the passage is locked or leads from another room
     */
    public function move(Passage $passage): bool
    {
        $this->moves++;

        if ($passage->getFromRoom()?->getId() !== $this->currentRoomId || !$this->isPassageOpen($passage)) {
            return false;
        }

        $this->currentRoomId = (int) $passage->getToRoom()?->getId();

        return true;
    }

    /**
     * Get the JSON representation of the game session.
     *
     * @return array<string, mixed> The JSON representation of the game session
     */
    public function jsonSerialize(): array
    {
        return [
            'playerName' => $this->playerName,
            'currentRoomId' => $this->currentRoomId,
            'backpack' => $this->backpack,
            'revealedItemIds' => $this->revealedItemIds,
            'unlockedPassageIds' => $this->unlockedPassageIds,
            'moves' => $this->moves,
        ];
    }
}
