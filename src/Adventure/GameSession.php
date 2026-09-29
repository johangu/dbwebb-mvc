<?php

namespace App\Adventure;

use App\Entity\Interaction;
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

    /** @var array<int> */
    private array $doneInteractionIds = [];

    /** @var array<int> */
    private array $consumedItemIds = [];

    /** @var array<int, string> Room images changed by interactions, by room id */
    private array $roomImages = [];

    private int $moves = 0;

    private bool $won = false;

    private bool $recorded = false;

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
     * Check if the player has found the treasure.
     *
     * @return bool True if the game is won, false otherwise
     */
    public function hasWon(): bool
    {
        return $this->won;
    }

    /**
     * Check if the game has been put on the highscore list.
     *
     * @return bool True if the game is recorded, false otherwise
     */
    public function isRecorded(): bool
    {
        return $this->recorded;
    }

    /**
     * Mark the game as put on the highscore list, so it is only recorded once.
     */
    public function markRecorded(): void
    {
        $this->recorded = true;
    }

    /**
     * Get the image to show for a room.
     *
     * An interaction may have changed how the room looks for the player,
     * otherwise the room's own image is used.
     *
     * @param Room $room The room to show
     *
     * @return string The file name of the image
     */
    public function getRoomImage(Room $room): string
    {
        return $this->roomImages[$room->getId()] ?? (string) $room->getImage();
    }

    /**
     * Check if an item can be seen by the player.
     *
     * An item is visible if it doesn't start hidden or if the player has
     * revealed it, as long as it hasn't been put in the backpack or used up.
     *
     * @param Item $item The item to check
     *
     * @return bool True if the item is visible, false otherwise
     */
    public function isItemVisible(Item $item): bool
    {
        if ($this->backpack->has($item) || in_array($item->getId(), $this->consumedItemIds, true)) {
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
     * Examine an item in the current room.
     *
     * If one of the interactions applies to examining the item it is performed,
     * otherwise nothing happens and the item's description is all there is to see.
     *
     * @param Item $item The item to examine
     * @param array<Interaction> $interactions The interactions that may apply
     *
     * @return Interaction|null The interaction that was performed, or null if none applied
     */
    public function examine(Item $item, array $interactions): ?Interaction
    {
        $this->moves++;

        if (!$this->isItemHere($item)) {
            return null;
        }

        return $this->perform($this->findInteraction($interactions, $item, null));
    }

    /**
     * Take an item in the current room and put it in the backpack.
     *
     * @param Item $item The item to take
     *
     * @return bool True if the item was put in the backpack, false otherwise
     */
    public function take(Item $item): bool
    {
        $this->moves++;

        if (!$this->isItemHere($item) || !$item->isPickable()) {
            return false;
        }

        $this->backpack->add($item);

        return true;
    }

    /**
     * Use an item from the backpack on an item in the current room.
     *
     * @param Item $usedItem The item from the backpack
     * @param Item $target The item to use it on
     * @param array<Interaction> $interactions The interactions that may apply
     *
     * @return Interaction|null The interaction that was performed, or null if none applied
     */
    public function use(Item $usedItem, Item $target, array $interactions): ?Interaction
    {
        $this->moves++;

        if (!$this->backpack->has($usedItem) || !$this->isItemHere($target)) {
            return null;
        }

        return $this->perform($this->findInteraction($interactions, $target, $usedItem));
    }

    /**
     * Check if an item is visible in the room the player is in.
     *
     * @param Item $item The item to check
     *
     * @return bool True if the item is here, false otherwise
     */
    private function isItemHere(Item $item): bool
    {
        return $item->getRoom()?->getId() === $this->currentRoomId && $this->isItemVisible($item);
    }

    /**
     * Find the first interaction that applies right now.
     *
     * An interaction applies if it is in the current room, has the right target
     * and used item, hasn't been done already and its required interaction is done.
     *
     * @param array<Interaction> $interactions The interactions to look through
     * @param Item $target The item acted upon
     * @param Item|null $usedItem The item used, or null when examining
     *
     * @return Interaction|null The interaction that applies, or null if none does
     */
    private function findInteraction(array $interactions, Item $target, ?Item $usedItem): ?Interaction
    {
        foreach ($interactions as $interaction) {
            $required = $interaction->getRequiredInteraction();

            if (
                $interaction->getRoom()?->getId() === $this->currentRoomId
                && $interaction->getTarget()?->getId() === $target->getId()
                && $interaction->getUsedItem()?->getId() === $usedItem?->getId()
                && !in_array($interaction->getId(), $this->doneInteractionIds, true)
                && ($required === null || in_array($required->getId(), $this->doneInteractionIds, true))
            ) {
                return $interaction;
            }
        }

        return null;
    }

    /**
     * Perform an interaction and record what it changed for the player.
     *
     * @param Interaction|null $interaction The interaction to perform
     *
     * @return Interaction|null The performed interaction, or null if there was none
     */
    private function perform(?Interaction $interaction): ?Interaction
    {
        if ($interaction === null) {
            return null;
        }

        $this->doneInteractionIds[] = (int) $interaction->getId();

        if ($interaction->getRevealsItem() !== null) {
            $this->revealedItemIds[] = (int) $interaction->getRevealsItem()->getId();
        }

        if ($interaction->getUnlocksPassage() !== null) {
            $this->unlockedPassageIds[] = (int) $interaction->getUnlocksPassage()->getId();
        }

        if ($interaction->getChangesRoomImage() !== null) {
            $this->roomImages[(int) $interaction->getRoom()?->getId()] = $interaction->getChangesRoomImage();
        }

        $usedItem = $interaction->getUsedItem();
        if ($usedItem !== null && $interaction->isConsumesUsedItem()) {
            $this->backpack->remove($usedItem);
            $this->consumedItemIds[] = (int) $usedItem->getId();
        }

        if ($interaction->isWins()) {
            $this->won = true;
        }

        return $interaction;
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
            'doneInteractionIds' => $this->doneInteractionIds,
            'consumedItemIds' => $this->consumedItemIds,
            'roomImages' => $this->roomImages,
            'moves' => $this->moves,
            'won' => $this->won,
        ];
    }
}
