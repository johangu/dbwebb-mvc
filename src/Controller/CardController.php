<?php

namespace App\Controller;

use App\Card\CardHand;
use App\DeckOfCards\StandardDeck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends AbstractController
{
    #[Route('/card', name: 'card')]
    public function card(): Response
    {
        return $this->render('card/index.html.twig');
    }

    #[Route('/card/deck', name: 'deck')]
    public function deck(SessionInterface $session): Response
    {
        if (! $session->has('deck')) {
            $session->set('deck', new StandardDeck);
        }

        $deck = $session->get('deck');

        return $this->render('card/deck.html.twig', [
            'deck' => $deck->getSorted(),
            'sorted' => true,
        ]);
    }

    #[Route('/card/deck/new', name: 'deck_new')]
    public function newDeck(SessionInterface $session): RedirectResponse
    {
        $deck = new StandardDeck;

        $session->set('deck', $deck);

        $this->addFlash('success', 'A new deck has been created');

        return $this->redirectToRoute('deck');
    }

    #[Route('/card/deck/shuffle', name: 'deck_shuffle')]
    public function shuffleDeck(SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $deck->shuffle();
        $session->set('deck', $deck);

        $this->addFlash('success', 'The deck has been shuffled');

        return $this->render('card/deck.html.twig', [
            'deck' => $deck->getCards(),
            'sorted' => false,
        ]);
    }

    #[Route('/card/deck/draw', name: 'deck_draw')]
    public function drawFromDeck(SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $hand = new CardHand($deck, 1);
        $session->set('deck', $deck);

        return $this->render('card/hand.html.twig', [
            'hand' => $hand,
        ]);
    }

    #[Route('/card/deck/draw/{number}', name: 'deck_draw_many')]
    public function drawManyFromDeck(int $number, SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $hand = new CardHand($deck, $number);
        $session->set('deck', $deck);
        $this->addFlash('success', 'You have drawn '.count($hand->getCards()).' cards');

        return $this->render('card/hand.html.twig', [
            'hand' => $hand,
        ]);
    }

    #[Route('/card/deck/deal/{players}/{cards}', name: 'deck_deal')]
    public function dealPlayerHands(int $players, int $cards, SessionInterface $session): Response
    {
        $hands = [];

        $deck = $session->get('deck');
        for ($i = 0; $i < $players; $i++) {
            $hands[$i] = new CardHand($deck, $cards);
        }
        $session->set('deck', $deck);

        return $this->render('card/hands.html.twig', [
            'deck' => $deck,
            'hands' => $hands,
        ]);
    }
}
