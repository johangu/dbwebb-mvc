<?php

namespace App\Adventure;

use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;

/**
 * What a player has changed in the world of the adventure.
 *
 * The world in the database never changes during play, so everything the player
 * has revealed, unlocked, used up or done is recorded here instead. Only ids are
 * kept, since the progress is stored in the session.
 *
 * @author  jogm23
 */
class Progress implements \JsonSerializable
{
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

    /**
     * Record what an interaction changed.
     *
     * @param Interaction $interaction The performed interaction
     */
    public function record(Interaction $interaction): void
    {
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
    }

    /**
     * Record that an item has been used up.
     *
     * @param Item $item The used up item
     */
    public function consume(Item $item): void
    {
        $this->consumedItemIds[] = (int) $item->getId();
    }

    /**
     * Check if an interaction can be performed.
     *
     * An interaction can only be performed once, and only after the
     * interaction it requires, if any, has been done.
     *
     * @param Interaction $interaction The interaction to check
     *
     * @return bool True if the interaction can be performed, false otherwise
     */
    public function isAvailable(Interaction $interaction): bool
    {
        $required = $interaction->getRequiredInteraction();

        return !in_array($interaction->getId(), $this->doneInteractionIds, true)
            && ($required === null || in_array($required->getId(), $this->doneInteractionIds, true));
    }

    /**
     * Check if an item has been revealed.
     *
     * @param Item $item The item to check
     *
     * @return bool True if the item has been revealed, false otherwise
     */
    public function isRevealed(Item $item): bool
    {
        return in_array($item->getId(), $this->revealedItemIds, true);
    }

    /**
     * Check if a passage has been unlocked.
     *
     * @param Passage $passage The passage to check
     *
     * @return bool True if the passage has been unlocked, false otherwise
     */
    public function isUnlocked(Passage $passage): bool
    {
        return in_array($passage->getId(), $this->unlockedPassageIds, true);
    }

    /**
     * Check if an item has been used up.
     *
     * @param Item $item The item to check
     *
     * @return bool True if the item has been used up, false otherwise
     */
    public function isConsumed(Item $item): bool
    {
        return in_array($item->getId(), $this->consumedItemIds, true);
    }

    /**
     * Get the image an interaction has changed a room to, if any.
     *
     * @param Room $room The room to check
     *
     * @return string|null The file name of the image, or null if the room looks as from the start
     */
    public function getRoomImage(Room $room): ?string
    {
        return $this->roomImages[$room->getId()] ?? null;
    }

    /**
     * Get the JSON representation of the progress.
     *
     * @return array<string, mixed> The JSON representation of the progress
     */
    public function jsonSerialize(): array
    {
        return [
            'revealedItemIds' => $this->revealedItemIds,
            'unlockedPassageIds' => $this->unlockedPassageIds,
            'doneInteractionIds' => $this->doneInteractionIds,
            'consumedItemIds' => $this->consumedItemIds,
            'roomImages' => $this->roomImages,
        ];
    }
}
