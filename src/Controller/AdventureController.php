<?php

namespace App\Controller;

use App\Adventure\GameSession;
use App\Entity\Hotspot;
use App\Repository\HotspotRepository;
use App\Repository\PassageRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdventureController extends AbstractController
{
    #[Route('/proj/play', name: 'proj_play', methods: ['GET'])]
    public function play(
        Request $request,
        RoomRepository $roomRepository,
        HotspotRepository $hotspotRepository,
        PassageRepository $passageRepository
    ): Response {
        $room = $roomRepository->findOneBy([], ['id' => 'ASC']);

        if ($room === null) {
            $this->addFlash('info', 'Spelet finns inte i databasen än, återställ databasen först');
            return $this->redirectToRoute('proj');
        }

        $game = new GameSession('Pirat', $room);

        // Only the hotspots the player can see, larger areas first so smaller ones end up on top
        $hotspots = array_filter(
            $hotspotRepository->findBy(['room' => $room]),
            fn (Hotspot $hotspot) => $hotspot->getItem() === null || $game->isItemVisible($hotspot->getItem())
        );
        usort($hotspots, fn (Hotspot $first, Hotspot $second) => $this->area($second) <=> $this->area($first));

        return $this->render('proj/play.html.twig', [
            'game' => $game,
            'room' => $room,
            'hotspots' => $hotspots,
            'passages' => $passageRepository->findBy(['fromRoom' => $room]),
            'debug' => $request->query->getBoolean('debug'),
        ]);
    }

    /**
     * Get the size of a hotspot, in percent of the image.
     */
    private function area(Hotspot $hotspot): float
    {
        return (float) $hotspot->getWidthPercent() * (float) $hotspot->getHeightPercent();
    }
}
