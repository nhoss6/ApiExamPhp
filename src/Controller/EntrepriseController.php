<?php
// src/Controller/EntrepriseController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EntrepriseController extends AbstractController
{
    private $client;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/recherche', name: 'recherche_entreprise')]
    public function recherche(Request $request): Response
    {

        $searchTerm = $request->query->get('searchTerm', '');
        $entreprises = [];

        if (!empty($searchTerm)) {
            $response = $this->client->request(
                'GET',
                'https://recherche-entreprises.api.gouv.fr/search',
                ['query' => ['q' => $searchTerm]]
            );

            if ($response->getStatusCode() === 200) {
                $data = $response->getContent();
                $donnees = json_decode($data, true);
                $entreprises = $donnees['results'] ?? [];
            }
        }


        return $this->render('recherche.html.twig', ['entreprises' => $entreprises, 'searchTerm' => $searchTerm,]);
    }
}
