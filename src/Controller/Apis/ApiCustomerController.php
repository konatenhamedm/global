<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CiviliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Civilite;
use App\Entity\Commande;
use App\Entity\Panneau;
use App\Repository\CiviliteRepository;
use App\Repository\CommandeRepository;
use App\Repository\PanneauRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/customer')]
class ApiCustomerController extends ApiInterface
{

    #[Route('/liste/panneaux', methods: ['GET'])]
    /**
     * Retourne la liste des civilites.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des panneaux avec leurs faces.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Panneau::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'customer')]
    // #[Security(name: 'Bearer')]
    public function index(PanneauRepository $panneauRepository): Response
    {
        try {

            $civilites = $panneauRepository->findAll();
            $response =  $this->responseData($civilites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/liste/commande/{IdClient}', methods: ['GET'])]
    /**
     * Liste des commmandes d'un customer.
     */
    #[OA\Response(
        response: 200,
        description: 'Liste des commmandes dun customer',
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
    #[OA\Tag(name: 'customer')]
    //#[Security(name: 'Bearer')]
    public function getListeCommandeCustomer(CommandeRepository $commandeRepository, $IdClient): Response
    {
        try {
            $commandes = $commandeRepository->findBy(['client' => $IdClient]);
            $response =  $this->responseData($commandes, 'group_commande', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
   

}
