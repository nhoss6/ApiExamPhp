<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StageController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/gratification', name: 'app_gratification')]
    public function calculerGratification(Request $request): Response
    {
        $data = null;

        // Récupérez les données de la gratification de stage depuis l'API
        $response = $this->client->request(
            'POST',
            'https://mon-entreprise.urssaf.fr/api/v1/evaluate',
            [
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'expressions' => ["salarié . contrat . stage . gratification minimale"],
                    'situation' => []
                ]
            ]
        );

        if ($response->getStatusCode() === 200) {
            // Convertissez la réponse JSON en tableau PHP
            $data = json_decode($response->getContent(), true);
        }

        return $this->render('gratification.html.twig', [
            'data' => $data,
        ]);
    }
}
