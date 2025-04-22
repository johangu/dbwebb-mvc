<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'session_debug')]
    public function session(SessionInterface $session): Response
    {
        return $this->render('session.html.twig');
    }

    #[Route('/session/delete', name: 'session_destroy')]
    public function destroySession(SessionInterface $session): RedirectResponse
    {
        $session->clear();
        $this->addFlash('success', 'Session has been destroyed');

        return $this->redirectToRoute('session_debug');
    }
}
