<?php

namespace App\Adventure;

use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * Loads the pirate adventure into the database.
 *
 * All content of the game, rooms, passages, items and interactions, is defined
 * here and written to the database through the ORM. Loading the world clears
 * the world tables first, so it can also be used to reset the database. The
 * highscores are kept, since they are not part of the world.
 *
 * @author  jogm23
 */
class WorldLoader
{
    private const array ROOMS = [
        'hamnen' => [
            'name' => 'Hamnen',
            'description' => 'Du står på kajen i en livlig hamn. Längs bryggan ligger lådor och rep utspridda, och en bit ifrån de andra står en ensam tunna. Norrut ligger en krog och österut ligger ett skepp förtöjt vid bryggan.',
            'image' => 'hamnen.webp',
        ],
        'krogen' => [
            'name' => 'Krogen',
            'description' => 'Krogen är mörk och full av rök. Vid ett runt bord sitter ett gäng sjömän och spelar kort, och på väggen hänger en tavla lite på sned. Dörren söderut leder tillbaka ut till hamnen.',
            'image' => 'krogen.webp',
        ],
        'skeppet' => [
            'name' => 'Skeppet',
            'description' => 'Du står på skeppets däck. I fören hänger ett tungt ankare och i aktern står ratten. Landgången västerut leder tillbaka till hamnen och österut väntar det öppna havet.',
            'image' => 'skeppet.webp',
        ],
        'havet' => [
            'name' => 'Havet',
            'description' => 'Skeppet seglar ut på det öppna havet med vinden i seglen. Långt borta vid horisonten skymtar en liten ö. Västerut kan du segla tillbaka in till hamnen.',
            'image' => 'havet.webp',
        ],
    ];

    private const array PASSAGES = [
        'hamnen-krogen' => ['from' => 'hamnen', 'to' => 'krogen', 'direction' => 'north', 'verb' => 'Gå', 'locked' => false],
        'krogen-hamnen' => ['from' => 'krogen', 'to' => 'hamnen', 'direction' => 'south', 'verb' => 'Gå', 'locked' => false],
        'hamnen-skeppet' => ['from' => 'hamnen', 'to' => 'skeppet', 'direction' => 'east', 'verb' => 'Gå', 'locked' => false],
        'skeppet-hamnen' => ['from' => 'skeppet', 'to' => 'hamnen', 'direction' => 'west', 'verb' => 'Gå', 'locked' => false],
        'skeppet-havet' => ['from' => 'skeppet', 'to' => 'havet', 'direction' => 'east', 'verb' => 'Segla', 'locked' => true],
        'havet-skeppet' => ['from' => 'havet', 'to' => 'skeppet', 'direction' => 'west', 'verb' => 'Segla', 'locked' => false],
    ];

    private const array ITEMS = [
        'tunnan' => [
            'room' => 'hamnen',
            'description' => 'En gammal väderbiten tunna som står lite för sig själv.',
            'hidden' => false,
            'pickable' => false,
        ],
        'penningpungen' => [
            'room' => 'hamnen',
            'description' => 'En tung pung full med mynt.',
            'hidden' => true,
            'pickable' => true,
        ],
        'tavlan' => [
            'room' => 'krogen',
            'description' => 'En oljemålning av ett stormigt hav som hänger lite på sned.',
            'hidden' => false,
            'pickable' => false,
        ],
        'skattkartan' => [
            'room' => 'krogen',
            'description' => 'En gammal karta med ett kryss utritat på en liten ö.',
            'hidden' => true,
            'pickable' => true,
        ],
        'sjömännen' => [
            'room' => 'krogen',
            'description' => 'Ett gäng rufsiga sjömän som ser ut att behöva både jobb och pengar.',
            'hidden' => false,
            'pickable' => false,
        ],
        'besättningen' => [
            'room' => 'krogen',
            'description' => 'Sjömännen från krogen, redo att segla.',
            'hidden' => true,
            'pickable' => true,
        ],
        'ankaret' => [
            'room' => 'skeppet',
            'description' => 'Ett stort ankare av järn, det är fortfarande nedsänkt.',
            'hidden' => false,
            'pickable' => false,
        ],
        'ratten' => [
            'room' => 'skeppet',
            'description' => 'Skeppets ratt i mörkt trä.',
            'hidden' => false,
            'pickable' => false,
        ],
        'horisonten' => [
            'room' => 'havet',
            'description' => 'Långt borta vid horisonten skymtar en liten ö.',
            'hidden' => false,
            'pickable' => false,
        ],
    ];

    private const array INTERACTIONS = [
        'undersok-tunnan' => [
            'room' => 'hamnen',
            'target' => 'tunnan',
            'usedItem' => null,
            'requires' => null,
            'reveals' => 'penningpungen',
            'unlocks' => null,
            'wins' => false,
            'message' => 'Du undersöker tunnan och hittar en penningpung gömd bakom den.',
        ],
        'undersok-tavlan' => [
            'room' => 'krogen',
            'target' => 'tavlan',
            'usedItem' => null,
            'requires' => null,
            'reveals' => 'skattkartan',
            'unlocks' => null,
            'wins' => false,
            'message' => 'Du lyfter på tavlan, bakom den hänger en gammal skattkarta.',
        ],
        'varva-sjomannen' => [
            'room' => 'krogen',
            'target' => 'sjömännen',
            'usedItem' => 'penningpungen',
            'requires' => null,
            'reveals' => 'besättningen',
            'unlocks' => null,
            'wins' => false,
            'message' => 'Sjömännen tar gärna emot pengarna och går med på att bli din besättning.',
        ],
        'hissa-ankaret' => [
            'room' => 'skeppet',
            'target' => 'ankaret',
            'usedItem' => 'besättningen',
            'requires' => null,
            'reveals' => null,
            'unlocks' => null,
            'wins' => false,
            'message' => 'Besättningen hjälps åt att hissa ankaret.',
        ],
        'ta-ratten' => [
            'room' => 'skeppet',
            'target' => 'ratten',
            'usedItem' => null,
            'requires' => 'hissa-ankaret',
            'reveals' => null,
            'unlocks' => 'skeppet-havet',
            'wins' => false,
            'message' => 'Du tar tag i ratten, skeppet är redo att lägga ut.',
        ],
        'hitta-skatten' => [
            'room' => 'havet',
            'target' => 'horisonten',
            'usedItem' => 'skattkartan',
            'requires' => null,
            'reveals' => null,
            'unlocks' => null,
            'wins' => true,
            'message' => 'Du följer skattkartan mot ön vid horisonten och hittar skatten.',
        ],
    ];

    private ObjectManager $manager;

    /** @var array<string, Room> */
    private array $rooms = [];

    /** @var array<string, Passage> */
    private array $passages = [];

    /** @var array<string, Item> */
    private array $items = [];

    /** @var array<string, Interaction> */
    private array $interactions = [];

    /**
     * Constructor
     *
     * @param  ManagerRegistry  $doctrine  The registry to get the entity manager from
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManager();
    }

    /**
     * Load the world into the database.
     *
     * Clears the world tables, but keeps the highscores, and adds
     * the rooms, passages, items and interactions of the adventure.
     */
    public function load(): void
    {
        $this->clear();

        $this->addRooms();
        $this->addPassages();
        $this->addItems();
        $this->addInteractions();

        $this->manager->flush();
    }

    /**
     * Remove the world from the database.
     *
     * Interactions are removed first since they refer to the other tables.
     */
    private function clear(): void
    {
        $classes = [Interaction::class, Item::class, Passage::class, Room::class];

        foreach ($classes as $class) {
            foreach ($this->manager->getRepository($class)->findAll() as $entity) {
                $this->manager->remove($entity);
            }
            $this->manager->flush();
        }
    }

    /**
     * Add all rooms of the adventure.
     */
    private function addRooms(): void
    {
        foreach (self::ROOMS as $key => $data) {
            $room = new Room();
            $room->setName($data['name']);
            $room->setDescription($data['description']);
            $room->setImage($data['image']);

            $this->manager->persist($room);
            $this->rooms[$key] = $room;
        }
    }

    /**
     * Add all passages between the rooms.
     */
    private function addPassages(): void
    {
        foreach (self::PASSAGES as $key => $data) {
            $passage = new Passage();
            $passage->setFromRoom($this->rooms[$data['from']]);
            $passage->setToRoom($this->rooms[$data['to']]);
            $passage->setDirection($data['direction']);
            $passage->setVerb($data['verb']);
            $passage->setLocked($data['locked']);

            $this->manager->persist($passage);
            $this->passages[$key] = $passage;
        }
    }

    /**
     * Add all items to their rooms.
     */
    private function addItems(): void
    {
        foreach (self::ITEMS as $name => $data) {
            $item = new Item();
            $item->setRoom($this->rooms[$data['room']]);
            $item->setName($name);
            $item->setDescription($data['description']);
            $item->setHidden($data['hidden']);
            $item->setPickable($data['pickable']);

            $this->manager->persist($item);
            $this->items[$name] = $item;
        }
    }

    /**
     * Add all interactions.
     *
     * An interaction may require an earlier one, so they are added in
     * the order they are defined.
     */
    private function addInteractions(): void
    {
        foreach (self::INTERACTIONS as $key => $data) {
            $interaction = new Interaction();
            $interaction->setRoom($this->rooms[$data['room']]);
            $interaction->setTarget($this->items[$data['target']]);
            $interaction->setUsedItem($data['usedItem'] ? $this->items[$data['usedItem']] : null);
            $interaction->setRequiredInteraction($data['requires'] ? $this->interactions[$data['requires']] : null);
            $interaction->setRevealsItem($data['reveals'] ? $this->items[$data['reveals']] : null);
            $interaction->setUnlocksPassage($data['unlocks'] ? $this->passages[$data['unlocks']] : null);
            $interaction->setWins($data['wins']);
            $interaction->setMessage($data['message']);

            $this->manager->persist($interaction);
            $this->interactions[$key] = $interaction;
        }
    }
}
