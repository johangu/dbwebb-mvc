<?php

namespace App\Adventure;

use App\Entity\Hotspot;
use App\Entity\Interaction;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * Loads the pirate adventure into the database.
 *
 * All content of the game, rooms, passages, items, hotspots and interactions, is defined
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
        'hamnen-krogen' => ['from' => 'hamnen', 'to' => 'krogen', 'direction' => 'north', 'verb' => 'Gå', 'startsLocked' => false],
        'krogen-hamnen' => ['from' => 'krogen', 'to' => 'hamnen', 'direction' => 'south', 'verb' => 'Gå', 'startsLocked' => false],
        'hamnen-skeppet' => ['from' => 'hamnen', 'to' => 'skeppet', 'direction' => 'east', 'verb' => 'Gå', 'startsLocked' => false],
        'skeppet-hamnen' => ['from' => 'skeppet', 'to' => 'hamnen', 'direction' => 'west', 'verb' => 'Gå', 'startsLocked' => false],
        'skeppet-havet' => ['from' => 'skeppet', 'to' => 'havet', 'direction' => 'east', 'verb' => 'Segla', 'startsLocked' => true],
        'havet-skeppet' => ['from' => 'havet', 'to' => 'skeppet', 'direction' => 'west', 'verb' => 'Segla', 'startsLocked' => false],
    ];

    private const array ITEMS = [
        'tunnan' => [
            'room' => 'hamnen',
            'description' => 'En gammal väderbiten tunna som står lite för sig själv.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'penningpungen' => [
            'room' => 'hamnen',
            'description' => 'En tung pung full med mynt.',
            'startsHidden' => true,
            'pickable' => true,
            'image' => 'items/penningpungen.webp',
        ],
        'lådorna' => [
            'room' => 'hamnen',
            'description' => 'Några tomma lastlådor, här finns inget av värde.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'nätet' => [
            'room' => 'hamnen',
            'description' => 'Ett gammalt fisknät som har sett bättre dagar.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'måsarna' => [
            'room' => 'hamnen',
            'description' => 'Måsarna skränar och cirklar över hamnen i jakt på fisk.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'flaggan' => [
            'room' => 'hamnen',
            'description' => 'En svart flagga med en dödskalle, skeppet ser ut att tillhöra pirater.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'tavlan' => [
            'room' => 'krogen',
            'description' => 'En oljemålning av ett stormigt hav som hänger lite på sned.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'skattkartan' => [
            'room' => 'krogen',
            'description' => 'En gammal karta med ett kryss utritat på en liten ö.',
            'startsHidden' => true,
            'pickable' => true,
            'image' => 'items/skattkartan.webp',
        ],
        'sjömännen' => [
            'room' => 'krogen',
            'description' => 'Ett gäng rufsiga sjömän som ser ut att behöva både jobb och pengar.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'besättningen' => [
            'room' => 'krogen',
            'description' => 'Sjömännen från krogen, redo att segla.',
            'startsHidden' => true,
            'pickable' => true,
            'image' => 'items/besattningen.webp',
        ],
        'brasan' => [
            'room' => 'krogen',
            'description' => 'Brasan sprakar och sprider värme i hela krogen.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'ljuskronan' => [
            'room' => 'krogen',
            'description' => 'En ljuskrona av järn med stearinljus som droppar ner på golvet.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'skylten' => [
            'room' => 'krogen',
            'description' => 'En skylt med en dödskalle och korslagda ben, krogens stolthet.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'stopet' => [
            'room' => 'krogen',
            'description' => 'Ett halvfullt stop med rom, någon har glömt det här.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'ankaret' => [
            'room' => 'skeppet',
            'description' => 'Ett stort ankare av järn, det är fortfarande nedsänkt.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'ratten' => [
            'room' => 'skeppet',
            'description' => 'Skeppets ratt i mörkt trä.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'ankarspelet' => [
            'room' => 'skeppet',
            'description' => 'Ankarspelet används för att hissa ankaret, men det krävs fler än en person för att veva det.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'gallret' => [
            'room' => 'skeppet',
            'description' => 'Ett galler över lastrummet, det är mörkt där nere.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'masten' => [
            'room' => 'skeppet',
            'description' => 'Stormasten är tjock som en ek och full av rep.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'repet' => [
            'room' => 'skeppet',
            'description' => 'Ett ordentligt ihoprullat rep som ligger vid relingen.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'horisonten' => [
            'room' => 'havet',
            'description' => 'Långt borta vid horisonten skymtar en liten ö.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'seglen' => [
            'room' => 'havet',
            'description' => 'Seglen är fyllda av vind och skeppet gör god fart.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'vågorna' => [
            'room' => 'havet',
            'description' => 'Vågorna slår mot skrovet och det salta vattnet stänker upp på däck.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
        'tunnorna' => [
            'room' => 'havet',
            'description' => 'Tunnor med proviant för resan, mest saltat fläsk och skeppsskorpor.',
            'startsHidden' => false,
            'pickable' => false,
            'image' => null,
        ],
    ];

    /**
     * Clickable areas in the room images, as [left, top, width, height] in percent.
     * Each area points at either an item or a passage.
     */
    private const array HOTSPOTS = [
        ['room' => 'hamnen', 'item' => 'tunnan', 'passage' => null, 'area' => [4, 66, 16, 32]],
        ['room' => 'hamnen', 'item' => 'penningpungen', 'passage' => null, 'area' => [20, 83, 8, 14]],
        ['room' => 'hamnen', 'item' => 'lådorna', 'passage' => null, 'area' => [14, 58, 11, 10]],
        ['room' => 'hamnen', 'item' => 'nätet', 'passage' => null, 'area' => [19, 69, 12, 13]],
        ['room' => 'hamnen', 'item' => 'måsarna', 'passage' => null, 'area' => [37, 4, 21, 16]],
        ['room' => 'hamnen', 'item' => 'flaggan', 'passage' => null, 'area' => [90, 1, 8, 14]],
        ['room' => 'hamnen', 'item' => null, 'passage' => 'hamnen-krogen', 'area' => [6, 41, 6, 16]],
        ['room' => 'hamnen', 'item' => null, 'passage' => 'hamnen-skeppet', 'area' => [64, 8, 35, 60]],
        ['room' => 'krogen', 'item' => 'tavlan', 'passage' => null, 'area' => [50, 22, 24, 32]],
        ['room' => 'krogen', 'item' => 'skattkartan', 'passage' => null, 'area' => [57, 30, 9, 16]],
        ['room' => 'krogen', 'item' => 'sjömännen', 'passage' => null, 'area' => [24, 52, 39, 32]],
        ['room' => 'krogen', 'item' => 'besättningen', 'passage' => null, 'area' => [40, 60, 9, 16]],
        ['room' => 'krogen', 'item' => 'brasan', 'passage' => null, 'area' => [0, 53, 10, 24]],
        ['room' => 'krogen', 'item' => 'ljuskronan', 'passage' => null, 'area' => [40, 10, 17, 18]],
        ['room' => 'krogen', 'item' => 'skylten', 'passage' => null, 'area' => [0, 18, 6, 11]],
        ['room' => 'krogen', 'item' => 'stopet', 'passage' => null, 'area' => [21, 81, 6, 12]],
        ['room' => 'krogen', 'item' => null, 'passage' => 'krogen-hamnen', 'area' => [80, 31, 15, 49]],
        ['room' => 'skeppet', 'item' => 'ankaret', 'passage' => null, 'area' => [83, 18, 12, 56]],
        ['room' => 'skeppet', 'item' => 'ratten', 'passage' => null, 'area' => [18, 13, 9, 13]],
        ['room' => 'skeppet', 'item' => 'ankarspelet', 'passage' => null, 'area' => [45, 49, 15, 28]],
        ['room' => 'skeppet', 'item' => 'gallret', 'passage' => null, 'area' => [46, 80, 26, 14]],
        ['room' => 'skeppet', 'item' => 'masten', 'passage' => null, 'area' => [33, 0, 8, 68]],
        ['room' => 'skeppet', 'item' => 'repet', 'passage' => null, 'area' => [71, 73, 10, 27]],
        ['room' => 'skeppet', 'item' => null, 'passage' => 'skeppet-hamnen', 'area' => [0, 62, 14, 38]],
        ['room' => 'skeppet', 'item' => null, 'passage' => 'skeppet-havet', 'area' => [58, 36, 24, 10]],
        ['room' => 'havet', 'item' => 'horisonten', 'passage' => null, 'area' => [58, 36, 42, 12]],
        ['room' => 'havet', 'item' => 'seglen', 'passage' => null, 'area' => [12, 0, 46, 45]],
        ['room' => 'havet', 'item' => 'vågorna', 'passage' => null, 'area' => [50, 62, 50, 38]],
        ['room' => 'havet', 'item' => 'tunnorna', 'passage' => null, 'area' => [37, 80, 12, 20]],
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
     * the rooms, passages, items, hotspots and interactions of the adventure.
     */
    public function load(): void
    {
        $this->clear();

        $this->addRooms();
        $this->addPassages();
        $this->addItems();
        $this->addHotspots();
        $this->addInteractions();

        $this->manager->flush();
    }

    /**
     * Remove the world from the database.
     *
     * Hotspots and interactions are removed first since they refer to the other tables.
     */
    private function clear(): void
    {
        $classes = [Hotspot::class, Interaction::class, Item::class, Passage::class, Room::class];

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
            $passage->setStartsLocked($data['startsLocked']);

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
            $item->setStartsHidden($data['startsHidden']);
            $item->setPickable($data['pickable']);
            $item->setImage($data['image']);

            $this->manager->persist($item);
            $this->items[$name] = $item;
        }
    }

    /**
     * Add the clickable areas of the room images.
     */
    private function addHotspots(): void
    {
        foreach (self::HOTSPOTS as $data) {
            [$left, $top, $width, $height] = $data['area'];

            $hotspot = new Hotspot();
            $hotspot->setRoom($this->rooms[$data['room']]);
            $hotspot->setItem($data['item'] ? $this->items[$data['item']] : null);
            $hotspot->setPassage($data['passage'] ? $this->passages[$data['passage']] : null);
            $hotspot->setLeftPercent($left);
            $hotspot->setTopPercent($top);
            $hotspot->setWidthPercent($width);
            $hotspot->setHeightPercent($height);

            $this->manager->persist($hotspot);
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
