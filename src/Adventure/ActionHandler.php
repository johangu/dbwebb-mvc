<?php

namespace App\Adventure;

use App\Entity\Hotspot;
use App\Entity\Item;
use App\Entity\Passage;
use App\Repository\InteractionRepository;

/**
 * Turns the player's clicks into actions in the game session.
 *
 * Looks up the interactions for what was clicked, lets the game session decide
 * what happens and returns the message to show the player.
 *
 * @author  jogm23
 */
class ActionHandler
{
    public const array VERBS = ['examine', 'take', 'use'];

    private InteractionRepository $interactions;

    /**
     * Constructor
     *
     * @param InteractionRepository $interactions The repository to find interactions in
     */
    public function __construct(InteractionRepository $interactions)
    {
        $this->interactions = $interactions;
    }

    /**
     * Handle a click on a hotspot with the selected verb.
     *
     * A hotspot on a passage always moves the player, whatever verb is selected.
     *
     * @param GameSession $game The player's game session
     * @param Hotspot $hotspot The clicked hotspot
     * @param string $verb The selected verb, one of VERBS
     * @param Item|null $usedItem The item from the backpack when the verb is use
     *
     * @return string The message to show the player, empty if there is nothing to say
     */
    public function click(GameSession $game, Hotspot $hotspot, string $verb, ?Item $usedItem = null): string
    {
        $passage = $hotspot->getPassage();
        if ($passage !== null) {
            return $this->move($game, $passage);
        }

        $item = $hotspot->getItem();
        if ($item === null) {
            return '';
        }

        return $this->act($game, $verb, $item, $usedItem);
    }

    /**
     * Do something with an item, examine it, take it or use another item on it.
     *
     * @param GameSession $game The player's game session
     * @param string $verb The verb, one of VERBS
     * @param Item $item The item to act on
     * @param Item|null $usedItem The item from the backpack when the verb is use
     *
     * @return string The message to show the player
     */
    public function act(GameSession $game, string $verb, Item $item, ?Item $usedItem = null): string
    {
        return match ($verb) {
            'take' => $this->take($game, $item),
            'use' => $this->use($game, $item, $usedItem),
            default => $this->examine($game, $item),
        };
    }

    /**
     * Move the player through a passage.
     *
     * @param GameSession $game The player's game session
     * @param Passage $passage The passage to move through
     *
     * @return string The message to show the player
     */
    public function move(GameSession $game, Passage $passage): string
    {
        if ($game->move($passage)) {
            return '';
        }

        return 'Det går inte att ta sig dit än.';
    }

    /**
     * Examine an item.
     *
     * @return string The message of the interaction, or the item's description
     */
    private function examine(GameSession $game, Item $item): string
    {
        $interaction = $game->examine($item, $this->interactions->findBy(['target' => $item]));

        return $interaction?->getMessage() ?? (string) $item->getDescription();
    }

    /**
     * Take an item.
     *
     * @return string The message to show the player
     */
    private function take(GameSession $game, Item $item): string
    {
        if ($game->take($item)) {
            return "Du tar {$item->getName()}.";
        }

        return "Det går inte att ta {$item->getName()}.";
    }

    /**
     * Use an item from the backpack on an item.
     *
     * @return string The message of the interaction, or that nothing happened
     */
    private function use(GameSession $game, Item $target, ?Item $usedItem): string
    {
        if ($usedItem === null) {
            return 'Välj först något i ryggsäcken att använda.';
        }

        $interaction = $game->use($usedItem, $target, $this->interactions->findBy(['target' => $target]));

        return $interaction?->getMessage() ?? 'Inget händer.';
    }
}
