<?php

namespace App\Controller;

use App\Card\CardHand;
use App\DeckOfCards\StandardDeck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DeckApiController extends AbstractController
{
    #[Route('/api/deck', name: 'api_deck')]
    public function apiDeck(SessionInterface $session): JsonResponse
    {
        if (! $session->has('deck')) {
            $session->set('deck', new StandardDeck());
        }

        $deck = $session->get('deck');

        $response = new JsonResponse($deck);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/shuffle', name: 'api_shuffle_deck', methods: ['POST'])]
    public function apiShuffleDeck(SessionInterface $session): JsonResponse
    {
        if (! $session->has('deck')) {
            return new JsonResponse(status: 404);
        }

        $deck = $session->get('deck');
        $deck->shuffle();
        $session->set('deck', $deck);

        $response = new JsonResponse($deck);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/draw/{number?1}', name: 'api_deck_draw', methods: ['POST'])]
    public function apiDrawCardFromDeck(SessionInterface $session, int $number = 1): JsonResponse
    {
        if (! $session->has('deck')) {
            return new JsonResponse(status: 404);
        }

        $deck = $session->get('deck');
        $cards = $deck->drawHand($number);
        $count = $deck->getCount();
        $session->set('deck', $deck);

        $response = new JsonResponse([
            'cards' => $cards,
            'cardsLeft' => $count,
        ]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/deal/{players?1}/{cards?1}', name: 'api_deck_deal', methods: ['POST'])]
    public function apiDealCards(SessionInterface $session, int $players = 1, int $cards = 1): JsonResponse
    {
        if (! $session->has('deck')) {
            return new JsonResponse(status: 404);
        }

        $deck = $session->get('deck');
        $hands = [];
        for ($i = 0; $i < $players; $i++) {
            $hands[$i] = new CardHand($deck, $cards);
        }
        $count = $deck->getCount();
        $session->set('deck', $deck);

        $response = new JsonResponse([
            'hands' => $hands,
            'cardsLeft' => $count,
        ]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
