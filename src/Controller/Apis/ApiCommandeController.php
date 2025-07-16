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
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/commande')]
class ApiCommandeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des commandes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commande::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commande')]
    // #[Security(name: 'Bearer')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        try {

            $commandes = $commandeRepository->findAll();

            $response =  $this->responseData($commandes, 'group_commande', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) commande en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) commande en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commande::class, groups: ['full']))

        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'commande')]
    //#[Security(name: 'Bearer')]
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


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) commande.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "libelle", type: "string"),
                        new OA\Property(property: "client", type: "string"),
                        new OA\Property(property: "impressionVisuelle", type: "boolean"),
                        new OA\Property(property: "dateDebut", type: "string"),
                        new OA\Property(property: "dateFin", type: "string"),
                        new OA\Property(
                            property: "lignes",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "face", type: "string"),


                                ]
                            ),
                        ),
                        new OA\Property(property: "userUpdate", type: "string"),

                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
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

        //dd($lignes);
        foreach ($lignes as $ligneData) {

            $face = $faceRepository->findOneBy(['code' => $ligneData['face']]);
            $somme += $face->getPrix();

            $ligne = new Ligne();
            $ligne->setFace($face);
            $ligne->setPrix($face->getPrix());
            $ligne->setDateDebut($dateDebut);
            $ligne->setDateFin($dateFin);
            $ligne->setCommande($commande);

            $ligneRepository->add($ligne); // sans flush pour le moment

            $face->setEtat(Face::ETAT['Reserve']);
            $face->setDateDebut($dateDebut);
            $face->setDateFin($dateFin);
            $faceRepository->add($face); // sans flush
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


    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) commande.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) commande',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commande::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'commande')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Commande $commande, CommandeRepository $villeRepository): Response
    {
        try {

            if ($commande != null) {

                $villeRepository->remove($commande, true);

                // On retourne la confirmation
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

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs commande.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commande::class, groups: ['full']))

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
