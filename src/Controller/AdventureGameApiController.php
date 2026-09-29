<?php

namespace App\Controller;

use App\Adventure\ActionHandler;
use App\Adventure\GameSession;
use App\Adventure\GameStatus;
use App\Repository\ItemRepository;
use App\Repository\PassageRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdventureGameApiController extends AbstractController
{
    #[Route('/proj/api/game', name: 'api_proj_game', methods: ['GET'])]
    public function apiGame(SessionInterface $session, GameStatus $status): JsonResponse
    {
        if (!$session->has('adventure')) {
            return $this->json(['error' => 'No game found'], Response::HTTP_NOT_FOUND);
        }

        /** @var GameSession $game */
        $game = $session->get('adventure');

        return $this->prettyJson($status->describe($game));
    }

    #[Route('/proj/api/start', name: 'api_proj_start', methods: ['POST'])]
    public function apiStart(
        Request $request,
        SessionInterface $session,
        GameStatus $status,
        RoomRepository $roomRepository
    ): JsonResponse {
        $name = trim($request->request->getString('name'));
        $room = $roomRepository->findOneBy([], ['id' => 'ASC']);

        if ($room === null) {
            return $this->json(['error' => 'The adventure is not loaded, reset the database'], Response::HTTP_NOT_FOUND);
        }

        if ($name === '') {
            return $this->json(['error' => 'A name is needed to play'], Response::HTTP_BAD_REQUEST);
        }

        $game = new GameSession($name, $room);
        $session->set('adventure', $game);

        return $this->prettyJson($status->describe($game));
    }

    #[Route('/proj/api/examine/{item}', name: 'api_proj_examine', methods: ['POST'])]
    public function apiExamine(
        string $item,
        SessionInterface $session,
        ActionHandler $actionHandler,
        GameStatus $status,
        ItemRepository $itemRepository
    ): JsonResponse {
        return $this->act($session, $actionHandler, $status, $itemRepository, 'examine', $item);
    }

    #[Route('/proj/api/take/{item}', name: 'api_proj_take', methods: ['POST'])]
    public function apiTake(
        string $item,
        SessionInterface $session,
        ActionHandler $actionHandler,
        GameStatus $status,
        ItemRepository $itemRepository
    ): JsonResponse {
        return $this->act($session, $actionHandler, $status, $itemRepository, 'take', $item);
    }

    #[Route('/proj/api/use/{item}/{target}', name: 'api_proj_use', methods: ['POST'])]
    public function apiUse(
        string $item,
        string $target,
        SessionInterface $session,
        ActionHandler $actionHandler,
        GameStatus $status,
        ItemRepository $itemRepository
    ): JsonResponse {
        return $this->act($session, $actionHandler, $status, $itemRepository, 'use', $target, $item);
    }

    #[Route('/proj/api/move/{direction}', name: 'api_proj_move', methods: ['POST'])]
    public function apiMove(
        string $direction,
        SessionInterface $session,
        ActionHandler $actionHandler,
        GameStatus $status,
        PassageRepository $passageRepository
    ): JsonResponse {
        if (!$session->has('adventure')) {
            return $this->json(['error' => 'No game found'], Response::HTTP_NOT_FOUND);
        }

        /** @var GameSession $game */
        $game = $session->get('adventure');
        $passage = $passageRepository->findOneBy([
            'fromRoom' => $game->getCurrentRoomId(),
            'direction' => $direction,
        ]);

        if ($passage === null) {
            return $this->json(['error' => 'There is no way in that direction'], Response::HTTP_NOT_FOUND);
        }

        $message = $actionHandler->move($game, $passage);
        $session->set('adventure', $game);

        return $this->prettyJson(['message' => $message] + $status->describe($game));
    }

    /**
     * Let the player do something with an item, given by name.
     *
     * @param SessionInterface $session The session with the player's game
     * @param ActionHandler $actionHandler Performs the action in the game
     * @param GameStatus $status Describes the game after the action
     * @param ItemRepository $itemRepository The repository to find the items in
     * @param string $verb The verb, one of ActionHandler::VERBS
     * @param string $itemName The name of the item to act on
     * @param string|null $usedItemName The name of the item from the backpack when the verb is use
     *
     * @return JsonResponse The message and the status of the game, or an error
     */
    private function act(
        SessionInterface $session,
        ActionHandler $actionHandler,
        GameStatus $status,
        ItemRepository $itemRepository,
        string $verb,
        string $itemName,
        ?string $usedItemName = null
    ): JsonResponse {
        if (!$session->has('adventure')) {
            return $this->json(['error' => 'No game found'], Response::HTTP_NOT_FOUND);
        }

        $item = $itemRepository->findOneBy(['name' => $itemName]);
        $usedItem = $usedItemName !== null ? $itemRepository->findOneBy(['name' => $usedItemName]) : null;

        if ($item === null || ($usedItemName !== null && $usedItem === null)) {
            return $this->json(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var GameSession $game */
        $game = $session->get('adventure');
        $message = $actionHandler->act($game, $verb, $item, $usedItem);
        $session->set('adventure', $game);

        return $this->prettyJson(['message' => $message] + $status->describe($game));
    }

    /**
     * Create a pretty printed JSON response.
     *
     * @param array<mixed> $data The data to put in the response
     *
     * @return JsonResponse The pretty printed response
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
