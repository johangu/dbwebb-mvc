<?php

namespace App\Adventure;

use App\Entity\Item;
use App\Entity\Passage;
use App\Repository\ItemRepository;
use App\Repository\PassageRepository;
use App\Repository\RoomRepository;

/**
 * Describes a player's game, as the player sees it.
 *
 * The game session only keeps ids, this class looks them up in the world
 * and tells what room the player is in, what can be seen there and what
 * is in the backpack. Used by the JSON API.
 *
 * @author  jogm23
 */
class GameStatus
{
    private RoomRepository $rooms;

    private PassageRepository $passages;

    private ItemRepository $items;

    /**
     * Constructor
     *
     * @param RoomRepository $rooms The rooms of the world
     * @param PassageRepository $passages The passages between the rooms
     * @param ItemRepository $items The items of the world
     */
    public function __construct(RoomRepository $rooms, PassageRepository $passages, ItemRepository $items)
    {
        $this->rooms = $rooms;
        $this->passages = $passages;
        $this->items = $items;
    }

    /**
     * Describe the game as the player sees it.
     *
     * @param GameSession $game The player's game session
     *
     * @return array<string, mixed> The status of the game
     */
    public function describe(GameSession $game): array
    {
        $room = $this->rooms->find($game->getCurrentRoomId());

        return [
            'player' => $game->getPlayerName(),
            'moves' => $game->getMoves(),
            'won' => $game->hasWon(),
            'room' => $room?->getName(),
            'description' => $room?->getDescription(),
            'items' => array_values(array_map(
                fn (Item $item) => $item->getName(),
                array_filter(
                    $this->items->findBy(['room' => $room]),
                    fn (Item $item) => $game->isItemVisible($item)
                )
            )),
            'exits' => array_map(fn (Passage $passage) => [
                'direction' => $passage->getDirection(),
                'verb' => $passage->getVerb(),
                'open' => $game->isPassageOpen($passage),
            ], $this->passages->findBy(['fromRoom' => $room])),
            'backpack' => array_map(
                fn (int $itemId) => $this->items->find($itemId)?->getName(),
                $game->getBackpack()->getItemIds()
            ),
        ];
    }
}
