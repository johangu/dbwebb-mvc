<?php

namespace App\Controller;

use App\TwentyOne\Game;
use App\TwentyOne\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/game', name: 'game')]
    public function game(): Response
    {
        return $this->render('game/index.html.twig');
    }

    #[Route('/game/doc', name: 'game_doc')]
    public function doc(): Response
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/new', name: 'game_new')]
    public function new(SessionInterface $session): Response
    {
        $game = new Game();
        $player = new Player('Player 1');
        $game->addPlayer($player);
        $session->set('game', $game);

        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/play', name: 'game_play')]
    public function play(SessionInterface $session): Response
    {
        if (!$session->has('game')) {
            return $this->redirectToRoute('game_new');
        }

        /** @var Game $game */
        $game = $session->get('game');

        return $this->render('game/play.html.twig', [
            'game' => $game,
            'players' => $game->getPlayers(),
            'winner' => $game->getWinner(),
        ]);
    }

    #[Route('/game/stand', name: 'game_stand')]
    public function stand(SessionInterface $session): Response
    {
        if (!$session->has('game')) {
            return $this->redirectToRoute('game_new');
        }

        /** @var Game $game */
        $game = $session->get('game');

        $game->stand();

        // If the current player is the bank, it will play its turn
        // here, rather than something more flashy in the view
        if ($game->getCurrentPlayer()->getName() === 'Bank') {
            $game->playBankTurn();
            $game->determineWinner();
        }

        $session->set('game', $game);

        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/deal', name: 'game_deal')]
    public function deal(SessionInterface $session): Response
    {
        if (!$session->has('game')) {
            return $this->redirectToRoute('game_new');
        }

        /** @var Game $game */
        $game = $session->get('game');

        $game->dealCard();
        $game->checkOutcome();

        $session->set('game', $game);

        return $this->redirectToRoute('game_play');
    }

    #[Route('/api/game', name: 'api_game')]
    public function api(SessionInterface $session): JsonResponse
    {
        if (!$session->has('game')) {
            return $this->json(['error' => 'No game found'], Response::HTTP_NOT_FOUND);
        }

        /** @var Game $game */
        $game = $session->get('game');

        $response = new JsonResponse([
            'players' => array_map(fn (Player $player) => [
                'name' => $player->getName(),
                'hand' => $player->getHand(),
                'score' => $game->getScore($player->getHand()),
            ], $game->getPlayers()),
            'winner' => $game->getWinner(),
        ]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
