<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\OrientationDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Orientation;
use App\Repository\OrientationRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/orientation')]
class ApiOrientationController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des orientations.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Orientation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'orientation')]
    // #[Security(name: 'Bearer')]
    public function index(OrientationRepository $orientationRepository): Response
    {
        try {

            $orientations = $orientationRepository->findAll();

          

            $response =  $this->responseData($orientations, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) orientation en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) orientation en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Orientation::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'orientation')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Orientation $orientation)
    {
        try {
            if ($orientation) {
                $response = $this->response($orientation);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($orientation);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) orientation.
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
    #[OA\Tag(name: 'orientation')]
    public function create(Request $request, OrientationRepository $orientationRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $orientation = new Orientation();
        $orientation->setLibelle($request->get('libelle'));
        $orientation->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $orientation->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $orientation->setCreatedAtValue(new DateTime());
        $orientation->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($orientation);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $orientationRepository->add($orientation, true);
        }

        return $this->responseData($orientation, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de orientation",
        description: "Permet de créer un orientation.",
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
    #[OA\Tag(name: 'orientation')]
    public function update(Request $request, Orientation $orientation, OrientationRepository $orientationRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($orientation != null) {

                $orientation->setLibelle($data->libelle);
                $orientation->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $orientation->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($orientation);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $orientationRepository->add($orientation, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($orientation, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) orientation.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) orientation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Orientation::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'orientation')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Orientation $orientation, OrientationRepository $villeRepository): Response
    {
        try {

            if ($orientation != null) {

                $villeRepository->remove($orientation, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($orientation);
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
     * Permet de supprimer plusieurs orientation.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Orientation::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'orientation')]
    public function deleteAll(Request $request, OrientationRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $orientation = $villeRepository->find($value['id']);

                if ($orientation != null) {
                    $villeRepository->remove($orientation);
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
