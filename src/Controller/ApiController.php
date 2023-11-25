<?php

namespace App\Controller;

use DirectoryIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_api_')]
class ApiController extends AbstractController
{
    #[Route('/edit', name: 'edit', methods: ['PATCH'])]
    public function edit(Request $request): Response
    {
        $data = $request->getContent();

        $data = json_decode($data, true);

        $siren = $data['siren'] ?? '';

        if (!$siren) {
            return $this->json(['Aucune entreprise avec'], 404);
        }

        $path = $this->getParameter('app.sauvegarde_data');

        $file = $path . '/' . $siren . '.txt';

        if (!file_exists($file)) {
            return $this->json(['Fichier non trouvé'], 400);
        }

        $ancienResultat = file_get_contents($file);

        $ancienResultat = json_decode($ancienResultat, true);

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $ancienResultat)) {
                $ancienResultat[$key] = $data[$key];
            }
        }

        // je vais encoder le resultat en json pour le sauvegarder
        $ancienResultat = json_encode($ancienResultat);

        file_put_contents($file, $ancienResultat);

        return $this->json(['Ok modifié'], 200);
    }


    #[Route('/delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $data = $request->getContent();

        $data = json_decode($data, true);

        $siren = $data['siren'] ?? '';

        if (!$siren) {
            return $this->json(['Aucune entreprise avec'], 404);
        }

        $path = $this->getParameter('app.sauvegarde_data');

        $file = $path . '/' . $siren . '.txt';

        if (!file_exists($file)) {
            return $this->json(['Fichier non trouvé'], 404);
        }

        unlink($file);

        return $this->json(['Ok supprimé'], 200);
    }

    #[Route('/api-ouverte-en-liste', name: 'api_ouverte_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $header = $request->headers->get('Content-Type');

        $siren = $request->query->get('siren', null);
        if ($siren) {
            $path = $this->getParameter('app.sauvegarde_data');

            $file = $path . '/' . $siren . '.txt';

            if (!file_exists($file)) {
                return $this->json(['Fichier non trouvé'], 400);
            }

            $resultat = file_get_contents($file);

            $resultat = json_decode($resultat, true);

            return $this->json([$resultat], 200);
        }

        $entreprises = [];

        $path = $this->getParameter('app.sauvegarde_data');

        $dossier = new DirectoryIterator($path);
        foreach ($dossier as $fichier) {

            // si c'est pas un "." ni ".."
            if ($fichier->isDot())
                continue; // "continue" permet de passer à l'itération suivante

            //si c'est pas un fichier
            if ($fichier->getType() != 'file')
                continue;

            $data = file_get_contents($path . '/' . $fichier->getFilename());
            $data = json_decode($data, true);

            $entreprises[] = $data;
        }

        return $this->json($entreprises, 200, ['Content-Type' => $request->headers->get('Content-Type')]);
    }

    #[Route('/api-ouverte-entreprise', name: 'api-ouverte-entreprise', methods: ['POST'])]
    public function createEntreprise(Request $request): Response
    {
        $content = $request->getContent();

        $data = json_decode($content, true);

        $siren = $data['siren'] ?? '';

        if (!$siren) {
            return $this->json(['Aucune entreprise avec'], 404);
        }

        $path = $this->getParameter('app.sauvegarde_data');

        $file = $path . '/' . $siren . '.txt';

        if (!file_exists($file)) {
            file_put_contents($file, $content);

            return $this->json(['Ok créer'], 201);
        }

        return $this->json(['Erreur: Existe déjà'], 409);
    }
}
