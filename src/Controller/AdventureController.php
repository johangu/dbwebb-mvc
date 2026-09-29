<?php

namespace App\Controller;

use App\Adventure\ActionHandler;
use App\Adventure\GameSession;
use App\Entity\Hotspot;
use App\Entity\Item;
use App\Entity\Room;
use App\Repository\HighscoreRepository;
use App\Repository\HotspotRepository;
use App\Repository\ItemRepository;
use App\Repository\PassageRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdventureController extends AbstractController
{
    #[Route('/proj/play', name: 'proj_play', methods: ['GET'])]
    public function play(
        Request $request,
        SessionInterface $session,
        RoomRepository $roomRepository,
        HotspotRepository $hotspotRepository,
        PassageRepository $passageRepository,
        ItemRepository $itemRepository
    ): Response {
        if (!$session->has('adventure')) {
            return $this->render('proj/start.html.twig');
        }

        /** @var GameSession $game */
        $game = $session->get('adventure');

        if ($game->hasWon()) {
            return $this->render('proj/win.html.twig', ['game' => $game]);
        }

        $room = $roomRepository->find($game->getCurrentRoomId());

        if ($room === null) {
            $session->remove('adventure');
            $this->addFlash('info', 'Spelet har återställts, börja om');
            return $this->redirectToRoute('proj_play');
        }

        $verb = $request->query->getString('verb', 'examine');
        if (!in_array($verb, ActionHandler::VERBS, true)) {
            $verb = 'examine';
        }

        return $this->render('proj/play.html.twig', [
            'game' => $game,
            'room' => $room,
            'hotspots' => $this->visibleHotspots($game, $hotspotRepository->findBy(['room' => $room])),
            'passages' => $passageRepository->findBy(['fromRoom' => $room]),
            'backpack' => $this->backpackItems($game, $itemRepository),
            'verb' => $verb,
            'usedItem' => $itemRepository->find($request->query->getInt('item')),
            'debug' => $request->query->getBoolean('debug'),
        ]);
    }

    #[Route('/proj/start', name: 'proj_start', methods: ['POST'])]
    public function start(Request $request, SessionInterface $session, RoomRepository $roomRepository): RedirectResponse
    {
        $name = trim($request->request->getString('name'));
        $room = $roomRepository->findOneBy([], ['id' => 'ASC']);

        if ($room === null) {
            $this->addFlash('info', 'Spelet finns inte i databasen än, återställ databasen först');
            return $this->redirectToRoute('proj');
        }

        if ($name === '') {
            $this->addFlash('error', 'Du måste ange ett namn för att kunna spela');
            return $this->redirectToRoute('proj_play');
        }

        $session->set('adventure', new GameSession($name, $room));

        return $this->redirectToRoute('proj_play');
    }

    #[Route('/proj/click', name: 'proj_click', methods: ['POST'])]
    public function click(
        Request $request,
        SessionInterface $session,
        ActionHandler $actionHandler,
        HotspotRepository $hotspotRepository,
        ItemRepository $itemRepository
    ): RedirectResponse {
        if (!$session->has('adventure')) {
            return $this->redirectToRoute('proj_play');
        }

        /** @var GameSession $game */
        $game = $session->get('adventure');
        $hotspot = $hotspotRepository->find($request->request->getInt('hotspot'));

        if ($hotspot !== null && $hotspot->getRoom()?->getId() === $game->getCurrentRoomId()) {
            $message = $actionHandler->click(
                $game,
                $hotspot,
                $request->request->getString('verb'),
                $itemRepository->find($request->request->getInt('item'))
            );
            $this->say($message);
            $session->set('adventure', $game);
        }

        return $this->redirectToRoute('proj_play');
    }

    #[Route('/proj/move', name: 'proj_move', methods: ['POST'])]
    public function move(
        Request $request,
        SessionInterface $session,
        ActionHandler $actionHandler,
        PassageRepository $passageRepository
    ): RedirectResponse {
        if (!$session->has('adventure')) {
            return $this->redirectToRoute('proj_play');
        }

        /** @var GameSession $game */
        $game = $session->get('adventure');
        $passage = $passageRepository->find($request->request->getInt('passage'));

        if ($passage !== null) {
            $this->say($actionHandler->move($game, $passage));
            $session->set('adventure', $game);
        }

        return $this->redirectToRoute('proj_play');
    }

    #[Route('/proj/highscore', name: 'proj_highscore', methods: ['GET'])]
    public function highscore(HighscoreRepository $highscoreRepository): Response
    {
        return $this->render('proj/highscore.html.twig', [
            'highscores' => $highscoreRepository->findBy([], ['moves' => 'ASC', 'createdAt' => 'ASC'], 10),
        ]);
    }

    #[Route('/proj/restart', name: 'proj_restart', methods: ['POST'])]
    public function restart(SessionInterface $session): RedirectResponse
    {
        $session->remove('adventure');

        return $this->redirectToRoute('proj_play');
    }

    /**
     * Show a message from the game over the scene.
     */
    private function say(string $message): void
    {
        if ($message !== '') {
            $this->addFlash('adventure', $message);
        }
    }

    /**
     * Get the hotspots the player can see, larger areas first so smaller ones end up on top.
     *
     * @param array<Hotspot> $hotspots
     *
     * @return array<Hotspot>
     */
    private function visibleHotspots(GameSession $game, array $hotspots): array
    {
        $visible = array_filter(
            $hotspots,
            fn (Hotspot $hotspot) => $hotspot->getItem() === null || $game->isItemVisible($hotspot->getItem())
        );
        usort($visible, fn (Hotspot $first, Hotspot $second) => $this->area($second) <=> $this->area($first));

        return $visible;
    }

    /**
     * Get the size of a hotspot, in percent of the image.
     */
    private function area(Hotspot $hotspot): float
    {
        return (float) $hotspot->getWidthPercent() * (float) $hotspot->getHeightPercent();
    }

    /**
     * Get the items in the backpack, in the order they were picked up.
     *
     * @return array<Item>
     */
    private function backpackItems(GameSession $game, ItemRepository $itemRepository): array
    {
        $items = [];
        foreach ($game->getBackpack()->getItemIds() as $itemId) {
            $item = $itemRepository->find($itemId);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
