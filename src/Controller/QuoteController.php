<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QuoteController extends AbstractController
{
    #[Route('/api/quote', name: 'api_quote')]
    public function apiQuote(): JsonResponse
    {
        $quotes = [
            'All warfare is based on deception.',
            'If you know the enemy and know yourself, you need not fear the result of a hundred battles.',
            'In the midst of chaos, there is also opportunity.',
        ];

        $response = new JsonResponse(
            [
                'quote' => $quotes[array_rand($quotes)],
                'date' => date('Y-m-d'),
                'timestamp' => time(),
            ]
        );

        return $response;
    }
}
