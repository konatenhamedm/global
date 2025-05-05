<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\IlluminationDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Illumination;
use App\Repository\IlluminationRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/illumination')]
class ApiIlluminationController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des illuminations.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Illumination::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'illumination')]
    // #[Security(name: 'Bearer')]
    public function index(IlluminationRepository $illuminationRepository): Response
    {
        try {

            $illuminations = $illuminationRepository->findAll();

          

            $response =  $this->responseData($illuminations, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) illumination en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) illumination en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Illumination::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'illumination')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Illumination $illumination)
    {
        try {
            if ($illumination) {
                $response = $this->response($illumination);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($illumination);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) illumination.
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
    #[OA\Tag(name: 'illumination')]
    public function create(Request $request, IlluminationRepository $illuminationRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $illumination = new Illumination();
        $illumination->setLibelle($data['libelle']);
        $illumination->setCreatedBy($this->userRepository->find($data['userUpdate']));
        $illumination->setUpdatedBy($this->userRepository->find($data['userUpdate']));
        $illumination->setCreatedAtValue(new DateTime());
        $illumination->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($illumination);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $illuminationRepository->add($illumination, true);
        }

        return $this->responseData($illumination, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de illumination",
        description: "Permet de créer un illumination.",
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
    #[OA\Tag(name: 'illumination')]
    public function update(Request $request, Illumination $illumination, IlluminationRepository $illuminationRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($illumination != null) {

                $illumination->setLibelle($data->libelle);
                $illumination->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $illumination->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($illumination);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $illuminationRepository->add($illumination, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($illumination, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) illumination.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) illumination',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Illumination::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'illumination')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Illumination $illumination, IlluminationRepository $villeRepository): Response
    {
        try {

            if ($illumination != null) {

                $villeRepository->remove($illumination, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($illumination);
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
     * Permet de supprimer plusieurs illumination.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Illumination::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'illumination')]
    public function deleteAll(Request $request, IlluminationRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $illumination = $villeRepository->find($value['id']);

                if ($illumination != null) {
                    $villeRepository->remove($illumination);
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
