<?php

namespace App\Controller;

use App\Adventure\WorldLoader;
use App\Entity\Highscore;
use App\Entity\Item;
use App\Entity\Passage;
use App\Entity\Room;
use App\Repository\HighscoreRepository;
use App\Repository\ItemRepository;
use App\Repository\PassageRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdventureApiController extends AbstractController
{
    private RoomRepository $roomRepository;

    private PassageRepository $passageRepository;

    private ItemRepository $itemRepository;

    public function __construct(
        RoomRepository $roomRepository,
        PassageRepository $passageRepository,
        ItemRepository $itemRepository
    ) {
        $this->roomRepository = $roomRepository;
        $this->passageRepository = $passageRepository;
        $this->itemRepository = $itemRepository;
    }

    #[Route('/proj/api', name: 'proj_api', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('proj/api.html.twig', [
            'firstRoom' => $this->roomRepository->findOneBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/proj/api/rooms', name: 'api_proj_rooms', methods: ['GET'])]
    public function apiRooms(): JsonResponse
    {
        return $this->prettyJson(array_map(
            fn (Room $room) => $this->roomData($room),
            $this->roomRepository->findAll()
        ));
    }

    #[Route('/proj/api/rooms/{id}', name: 'api_proj_room', methods: ['GET'])]
    public function apiRoom(int $id): JsonResponse
    {
        $room = $this->roomRepository->find($id);

        if ($room === null) {
            return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->prettyJson($this->roomData($room));
    }

    #[Route('/proj/api/highscore', name: 'api_proj_highscore', methods: ['GET'])]
    public function apiHighscore(HighscoreRepository $highscoreRepository): JsonResponse
    {
        return $this->prettyJson(array_map(
            fn (Highscore $highscore) => [
                'name' => $highscore->getName(),
                'moves' => $highscore->getMoves(),
                'date' => $highscore->getCreatedAt()?->format(DATE_ATOM),
            ],
            $highscoreRepository->findBy([], ['moves' => 'ASC', 'createdAt' => 'ASC'], 10)
        ));
    }

    #[Route('/proj/api/reset', name: 'api_proj_reset', methods: ['POST'])]
    public function apiReset(WorldLoader $worldLoader, SessionInterface $session): JsonResponse
    {
        $worldLoader->load();
        $session->remove('adventure');

        return $this->prettyJson([
            'message' => 'The adventure has been reset',
            'rooms' => count($this->roomRepository->findAll()),
            'items' => count($this->itemRepository->findAll()),
        ]);
    }

    /**
     * Get a room with its exits and items as an array.
     *
     * @return array<string, mixed>
     */
    private function roomData(Room $room): array
    {
        return [
            'id' => $room->getId(),
            'name' => $room->getName(),
            'description' => $room->getDescription(),
            'image' => $room->getImage(),
            'exits' => array_map(fn (Passage $passage) => [
                'direction' => $passage->getDirection(),
                'verb' => $passage->getVerb(),
                'to' => $passage->getToRoom()?->getName(),
                'startsLocked' => $passage->isStartsLocked(),
            ], $this->passageRepository->findBy(['fromRoom' => $room])),
            'items' => array_map(fn (Item $item) => [
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'startsHidden' => $item->isStartsHidden(),
                'pickable' => $item->isPickable(),
            ], $this->itemRepository->findBy(['room' => $room])),
        ];
    }

    /**
     * Create a pretty printed JSON response.
     *
     * @param array<mixed> $data
     */
    private function prettyJson(array $data): JsonResponse
    {
        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
