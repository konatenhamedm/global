<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CommandeDTO;
use App\Entity\AvecImpression;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Commande;
use App\Entity\Face;
use App\Entity\Ligne;
use App\Entity\SansImpression;
use App\Repository\AvecImpressionRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\FaceRepository;
use App\Repository\LigneRepository;
use App\Repository\SansImpressionRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/commande')]
class ApiCommandeController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister toutes les commandes",
        description: "Retourne la liste complète de toutes les commandes publicitaires avec leurs lignes, client et état."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des commandes récupérée avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "code", type: "string", example: "COMD-20250101-0001"),
                            new OA\Property(property: "libelle", type: "string", example: "COMD-20250101-0001"),
                            new OA\Property(property: "etat", type: "string", enum: ["devis_attente", "proforma_attente_validation", "contrat_attente_creation", "contrat_attente_validation", "contrat_en_cours", "contrat_cloture"], example: "devis_attente"),
                            new OA\Property(property: "impressionVisuelle", type: "string", enum: ["avec", "sans"], example: "avec"),
                            new OA\Property(property: "montantProvisoire", type: "number", format: "float", example: 3000000),
                            new OA\Property(property: "montant", type: "number", format: "float", nullable: true, example: 3200000),
                            new OA\Property(property: "montantLocation", type: "number", format: "float", nullable: true, example: 3000000),
                            new OA\Property(property: "montantImpression", type: "number", format: "float", nullable: true, example: 200000),
                            new OA\Property(property: "montantPose", type: "number", format: "float", nullable: true, example: 0),
                            new OA\Property(property: "dateDebut", type: "string", format: "date", example: "2025-01-15"),
                            new OA\Property(property: "dateFin", type: "string", format: "date", example: "2025-03-15"),
                            new OA\Property(property: "nombreJour", type: "integer", example: 59),
                            new OA\Property(property: "client", type: "object", properties: [
                                new OA\Property(property: "id", type: "integer", example: 3),
                                new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                            ]),
                            new OA\Property(property: "lignes", type: "array", items: new OA\Items(type: "object")),
                        ]
                    )
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur interne du serveur",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 500),
                new OA\Property(property: "message", type: "string", example: "Erreur interne du serveur"),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Tag(name: 'commande')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        try {
            $commandes = $commandeRepository->findAll();
            $response =  $this->responseData($commandes, 'group_commande', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir une commande par ID",
        description: "Retourne les détails complets d'une commande publicitaire à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de la commande",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Commande trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "code", type: "string", example: "COMD-20250101-0001"),
                        new OA\Property(property: "etat", type: "string", example: "devis_attente"),
                        new OA\Property(property: "impressionVisuelle", type: "string", enum: ["avec", "sans"], example: "avec"),
                        new OA\Property(property: "montantProvisoire", type: "number", example: 3000000),
                        new OA\Property(property: "dateDebut", type: "string", format: "date", example: "2025-01-15"),
                        new OA\Property(property: "dateFin", type: "string", format: "date", example: "2025-03-15"),
                        new OA\Property(property: "client", type: "object"),
                        new OA\Property(property: "lignes", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "avecImpression", type: "object", nullable: true),
                        new OA\Property(property: "sansImpression", type: "object", nullable: true),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Commande non trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'commande')]
    public function getOne(?Commande $commande)
    {
        try {
            if ($commande) {
                $response = $this->response($commande);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($commande);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }


    private function code()
    {
        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(Commande::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        if ($nb == 0) {
            $nb = 1;
        } else {
            $nb = $nb + 1;
        }
        return str_pad("COMD-" . $nb, 3, '0', STR_PAD_LEFT);
    }

    private function generateLibelleParJour(): string
    {
        $date = (new \DateTime())->format('Ymd');

        $query = $this->em->createQueryBuilder()
            ->select('count(c.id)')
            ->from(Commande::class, 'c')
            ->where('c.dateCommande BETWEEN :start AND :end')
            ->setParameter('start', (new \DateTime())->setTime(0, 0, 0))
            ->setParameter('end', (new \DateTime())->setTime(23, 59, 59));

        $count = (int) $query->getQuery()->getSingleScalarResult();
        $count++;

        return sprintf('COMD-%s-%04d', $date, $count);
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer une nouvelle commande publicitaire",
        description: "Crée une commande pour un client en réservant une ou plusieurs faces de panneaux. Le libellé et le code sont générés automatiquement. Selon la valeur de `impressionVisuelle`, un workflow `avecImpression` (8 étapes) ou `sansImpression` (5 étapes) est initialisé.\n\n**États possibles de la commande :**\n- `devis_attente` → état initial\n- `proforma_attente_validation`\n- `contrat_attente_creation`\n- `contrat_attente_validation`\n- `contrat_en_cours`\n- `contrat_cloture`",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['client', 'impressionVisuelle', 'dateDebut', 'dateFin', 'lignes', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "client", type: "integer", description: "ID du client", example: 3),
                        new OA\Property(property: "impressionVisuelle", type: "string", description: "Type d'impression", enum: ["avec", "sans"], example: "avec"),
                        new OA\Property(property: "dateDebut", type: "string", format: "date", description: "Date de début de la réservation (YYYY-MM-DD)", example: "2025-01-15"),
                        new OA\Property(property: "dateFin", type: "string", format: "date", description: "Date de fin de la réservation (YYYY-MM-DD)", example: "2025-03-15"),
                        new OA\Property(
                            property: "lignes",
                            type: "array",
                            description: "Liste des faces à réserver (par code alphanumérique de la face)",
                            items: new OA\Items(
                                type: "object",
                                required: ['face'],
                                properties: [
                                    new OA\Property(property: "face", type: "string", description: "Code de la face à réserver", example: "FACE-001"),
                                ]
                            )
                        ),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui crée la commande", example: 1),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Commande créée avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 5),
                        new OA\Property(property: "code", type: "string", example: "COMD-20250109-0001"),
                        new OA\Property(property: "libelle", type: "string", example: "COMD-20250109-0001"),
                        new OA\Property(property: "etat", type: "string", example: "devis_attente"),
                        new OA\Property(property: "impressionVisuelle", type: "string", example: "avec"),
                        new OA\Property(property: "montantProvisoire", type: "number", example: 3000000),
                        new OA\Property(property: "dateDebut", type: "string", format: "date", example: "2025-01-15"),
                        new OA\Property(property: "dateFin", type: "string", format: "date", example: "2025-03-15"),
                        new OA\Property(property: "nombreJour", type: "integer", example: 59),
                        new OA\Property(property: "avecImpression", type: "object", nullable: true, description: "Présent si impressionVisuelle = 'avec'"),
                        new OA\Property(property: "sansImpression", type: "object", nullable: true, description: "Présent si impressionVisuelle = 'sans'"),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Données invalides (client introuvable, face déjà réservée, dates invalides, etc.)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 400),
                new OA\Property(property: "message", type: "string", example: "Validation failed"),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le client est obligatoire.", "La date de fin doit être après la date de début."]),
            ]
        )
    )]
    #[OA\Tag(name: 'commande')]
    public function create(
        Request $request,
        CommandeRepository $commandeRepository,
        ClientRepository $clientRepository,
        FaceRepository $faceRepository,
        LigneRepository $ligneRepository,
        AvecImpressionRepository $avecImpressionRepository,
        SansImpressionRepository $sansImpressionRepository,

    ): Response {

        $data = json_decode($request->getContent(), true);
        $commande = new Commande();
        $commande->setLibelle($this->generateLibelleParJour());
        $commande->setImpressionVisuelle($request->get('impressionVisuelle'));
        $commande->setClient($clientRepository->find($request->get('client')));
        $commande->setCode($this->code());

        $dateDebut = new \DateTime($request->get('dateDebut'));
        $dateFin = new \DateTime($request->get('dateFin'));

        $interval = $dateDebut->diff($dateFin);
        $nombreDeJours = $interval->days;

        $commande->setDateDebut($dateDebut);
        $commande->setDateFin($dateFin);
        $commande->setNombreJour($nombreDeJours);

        $user = $this->userRepository->find($request->get('userUpdate'));
        $commande->setCreatedBy($user);
        $commande->setUpdatedBy($user);

        $commande->setCreatedAtValue(new \DateTime());
        $commande->setUpdatedAt(new \DateTime());

        $errorResponse = $this->errorResponse($commande);
        if ($errorResponse !== null) {
            return $errorResponse;
        }
        $commandeRepository->add($commande, false);

        $somme = 0;
        $lignes = $request->get('lignes');

        foreach ($lignes as $ligneData) {

            $face = $faceRepository->findOneBy(['code' => $ligneData['face']]);
            $somme += $face->getPrix();

            $ligne = new Ligne();
            $ligne->setFace($face);
            $ligne->setPrix($face->getPrix());
            $ligne->setDateDebut($dateDebut);
            $ligne->setDateFin($dateFin);
            $ligne->setCommande($commande);

            $ligneRepository->add($ligne);

            $face->setEtat(Face::ETAT['Reserve']);
            $face->setDateDebut($dateDebut);
            $face->setDateFin($dateFin);
            $faceRepository->add($face);
        }

        $commande->setMontantProvisoire($somme);

        if ($request->get('impressionVisuelle') == "avec") {

            $avecImpression = new AvecImpression();
            $avecImpression->setEtape('etape_1');
            $avecImpression->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
            $avecImpression->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
            $avecImpression->setCreatedAtValue(new DateTime());
            $avecImpression->setUpdatedAt(new DateTime());
            $avecImpressionRepository->add($avecImpression, true);

            $commande->setAvecImpression($avecImpression);
            $commandeRepository->add($commande, true);
        } else {
            $sansImpression = new SansImpression();
            $sansImpression->setEtape('etape_1');
            $sansImpression->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
            $sansImpression->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
            $sansImpression->setCreatedAtValue(new DateTime());
            $sansImpression->setUpdatedAt(new DateTime());

            $sansImpressionRepository->add($sansImpression, true);
            $commande->setSansImpression($sansImpression);
            $commandeRepository->add($commande, true);
        }

        return $this->responseData($commande, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer une commande",
        description: "Supprime définitivement une commande à partir de son identifiant. ⚠️ Cette action supprime aussi les lignes et les étapes de validation associées."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de la commande à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Commande supprimée avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Commande non trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'commande')]
    public function delete(Request $request, Commande $commande, CommandeRepository $villeRepository): Response
    {
        try {
            if ($commande != null) {
                $villeRepository->remove($commande, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($commande);
            } else {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }

    #[Route('/delete/all', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer plusieurs commandes",
        description: "Supprime une liste de commandes en passant leurs IDs dans le corps de la requête.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ids",
                        type: "array",
                        description: "Liste des IDs commandes à supprimer",
                        items: new OA\Items(
                            type: "object",
                            properties: [new OA\Property(property: "id", type: "integer", example: 1)]
                        ),
                        example: [["id" => 1], ["id" => 2]]
                    ),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Commandes supprimées avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'commande')]
    public function deleteAll(Request $request, CommandeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $commande = $villeRepository->find($value['id']);

                if ($commande != null) {
                    $villeRepository->remove($commande);
                }
            }
            $this->setMessage("Operation effectuées avec success");
            $response = $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }
}
