<?php

namespace App\Adventure;

use App\Entity\Item;

/**
 * The player's backpack in the adventure.
 *
 * Only the ids of the items are kept, since the backpack is stored in the session.
 *
 * @author  jogm23
 */
class Backpack implements \JsonSerializable
{
    /** @var array<int> */
    private array $itemIds = [];

    /**
     * Put an item in the backpack.
     *
     * @param Item $item The item to add
     */
    public function add(Item $item): void
    {
        if ($this->has($item)) {
            return;
        }

        $this->itemIds[] = (int) $item->getId();
    }

    /**
     * Check if an item is in the backpack.
     *
     * @param Item $item The item to look for
     *
     * @return bool True if the item is in the backpack, false otherwise
     */
    public function has(Item $item): bool
    {
        return in_array($item->getId(), $this->itemIds, true);
    }

    /**
     * Get the ids of the items in the backpack.
     *
     * @return array<int> The item ids, in the order they were added
     */
    public function getItemIds(): array
    {
        return $this->itemIds;
    }

    /**
     * Get the JSON representation of the backpack.
     *
     * @return array<string, mixed> The JSON representation of the backpack
     */
    public function jsonSerialize(): array
    {
        return [
            'itemIds' => $this->itemIds,
        ];
    }
}
