<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SalaireController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/salaire', name: 'app_salaire')]
    public function calculerSalaire(Request $request): Response
    {
        $salaireBrut = $request->request->get('salaireBrut', 0);

        $data = null;
        if ($salaireBrut) {
            $response = $this->client->request(
                'POST',
                'https://mon-entreprise.urssaf.fr/api/v1/evaluate',
                [
                    'json' => [
                        'situation' => [
                            'salarié . contrat . salaire brut' => [
                                'valeur' => $salaireBrut,
                                'unité' => '€ / mois'
                            ],
                            'salarié . contrat' => "'CDI'"
                        ],
                        'expressions' => [
                            'salarié . rémunération . net . à payer avant impôt',
                            'salarié . coût total employeur'
                            // Ajoutez d'autres expressions si nécessaire
                        ]
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
            }
        }

        return $this->render('calcul_salaire.html.twig', [
            'data' => $data,
            'salaireBrut' => $salaireBrut
        ]);
    }
}
