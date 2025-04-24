<?php

namespace App\Controller;

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
            $session->set('deck', new StandardDeck);
        }

        $deck = $session->get('deck');

        $response = new JsonResponse($deck);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
