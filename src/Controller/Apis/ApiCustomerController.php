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
use App\Repository\IlluminationRepository;
use App\Repository\OrientationRepository;
use App\Repository\PanneauRepository;
use App\Repository\SousTypeRepository;
use App\Repository\SpecificationRepository;
use App\Repository\SubstratRepository;
use App\Repository\SuperficieRepository;
use App\Repository\TailleRepository;
use App\Repository\TypeClientRepository;
use App\Repository\TypeRepository;
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


    #[Route('/parametres', methods: ['GET'])]
    /**
     * Retourne la liste des clients.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Panneau::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'customer')]
    // #[Security(name: 'Bearer')]
    public function indexParametre(
        TypeClientRepository $typeClientRepository,
        TailleRepository $tailleRepository,
        IlluminationRepository $illuminationRepository,
        SousTypeRepository $sousTypeRepository,
        SpecificationRepository $specificationRepository,
        OrientationRepository $orientationRepository,
        SubstratRepository $substratRepository,
        SuperficieRepository $superficieRepository,
        TypeRepository $typeRepository

    ): Response {
        try {
            $tailles = [];
            $illuminations = [];
            $substrats = [];
            $specifications = [];
            $panneauTypes = [];
            $panneauSousTypes = [];
            $superficies = [];
            $orientations = [];

            foreach ($tailleRepository->findAll() as $key => $taille) {
                $tailles[] = [
                    'id' => $taille->getId(),
                    'libelle' => $taille->getDimenssions(),
                ];
            }

            foreach ($illuminationRepository->findAll() as $key => $illumination) {
                $illuminations[] = [
                    'id' => $illumination->getId(),
                    'libelle' => $illumination->getLibelle()
                ];
            }

            foreach ($substratRepository->findAll() as $key => $substrat) {
                $substrats[] = [
                    'id' => $substrat->getId(),
                    'libelle' => $substrat->getLibelle(),
                    'code' => $substrat->getCode(),
                ];
            }
            foreach ($specificationRepository->findAll() as $key => $specification) {
                $specifications[] = [
                    'id' => $specification->getId(),
                    'libelle' => $specification->getLibelle(),
                    'code' => $specification->getCode(),
                ];
            }
            foreach ($typeRepository->findAll() as $key => $type) {
                $panneauTypes[] = [
                    'id' => $type->getId(),
                    'libelle' => $type->getLibelle(),
                ];
            }

            foreach ($sousTypeRepository->findAll() as $key => $sousType) {
                $panneauSousTypes[] = [
                    'id' => $sousType->getId(),
                    'libelle' => $sousType->getLibelle(),
                ];
            }

            foreach ($superficieRepository->findAll() as  $superficie) {
                $superficies[] = [
                    'id' =>  $superficie->getId(),
                    'libelle' =>  $superficie->getLibelle(),

                ];
            }

            foreach ($orientationRepository->findAll() as  $orientation) {
                $orientations[] = [
                    'id' =>  $orientation->getId(),
                    'libelle' =>  $orientation->getLibelle()
                ];
            }


            $data = [
                'tailles' => $tailles,
                'illuminations' => $illuminations,
                'substrats' => $substrats,
                'specifications' => $specifications,
                'panneauTypes' => $panneauTypes,
                'panneauSousTypes' => $panneauSousTypes,
                'superficies' => $superficies,
                'oriantation' => $orientations,
            ];

            $response = $this->responseData($data, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

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
