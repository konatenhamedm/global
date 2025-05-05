<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\SousTypeDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\SousType;
use App\Repository\SousTypeRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/sousType')]
class ApiSousTypeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des sousTypes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SousType::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'sousType')]
    // #[Security(name: 'Bearer')]
    public function index(SousTypeRepository $sousTypeRepository): Response
    {
        try {

            $sousTypes = $sousTypeRepository->findAll();

          

            $response =  $this->responseData($sousTypes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) sousType en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) sousType en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SousType::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'sousType')]
    //#[Security(name: 'Bearer')]
    public function getOne(?SousType $sousType)
    {
        try {
            if ($sousType) {
                $response = $this->response($sousType);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($sousType);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) sousType.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'sousType')]
    public function create(Request $request, SousTypeRepository $sousTypeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $sousType = new SousType();
        $sousType->setLibelle($data['libelle']);
        $sousType->setCreatedBy($this->userRepository->find($data['userUpdate']));
        $sousType->setUpdatedBy($this->userRepository->find($data['userUpdate']));
        $sousType->setCreatedAtValue(new DateTime());
        $sousType->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($sousType);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $sousTypeRepository->add($sousType, true);
        }

        return $this->responseData($sousType, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de sousType",
        description: "Permet de créer un sousType.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'sousType')]
    public function update(Request $request, SousType $sousType, SousTypeRepository $sousTypeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($sousType != null) {

                $sousType->setLibelle($data->libelle);
                $sousType->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $sousType->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($sousType);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $sousTypeRepository->add($sousType, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($sousType, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage("Cette ressource est inexsitante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) sousType.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) sousType',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SousType::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'sousType')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, SousType $sousType, SousTypeRepository $villeRepository): Response
    {
        try {

            if ($sousType != null) {

                $villeRepository->remove($sousType, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($sousType);
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
     * Permet de supprimer plusieurs sousType.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SousType::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'sousType')]
    public function deleteAll(Request $request, SousTypeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $sousType = $villeRepository->find($value['id']);

                if ($sousType != null) {
                    $villeRepository->remove($sousType);
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
