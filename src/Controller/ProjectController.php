<?php

namespace App\Controller;

use App\Adventure\WorldLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route('/proj', name: 'proj')]
    public function project(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route('/proj/about', name: 'proj_about')]
    public function about(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    #[Route('/proj/about/database', name: 'proj_about_database')]
    public function database(): Response
    {
        return $this->render('proj/database.html.twig');
    }

    #[Route('/proj/cheat', name: 'proj_cheat')]
    public function cheat(): Response
    {
        return $this->render('proj/cheat.html.twig');
    }

    #[Route('/proj/reset', name: 'proj_reset', methods: ['POST'])]
    public function reset(WorldLoader $worldLoader, SessionInterface $session): RedirectResponse
    {
        $worldLoader->load();
        $session->remove('adventure');

        $this->addFlash('success', 'Databasen har återställts');

        return $this->redirectToRoute('proj');
    }
}
