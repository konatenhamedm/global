<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TypeDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Type;
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

#[Route('/api/type')]
class ApiTypeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des types.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Type::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'type')]
    // #[Security(name: 'Bearer')]
    public function index(TypeRepository $typeRepository): Response
    {
        try {

            $types = $typeRepository->findAll();

          

            $response =  $this->responseData($types, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) type en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) type en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Type::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'type')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Type $type)
    {
        try {
            if ($type) {
                $response = $this->response($type);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($type);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) type.
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
    #[OA\Tag(name: 'type')]
    public function create(Request $request, TypeRepository $typeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $type = new Type();
        $type->setLibelle($data['libelle']);
        $type->setCreatedBy($this->userRepository->find($data['userUpdate']));
        $type->setUpdatedBy($this->userRepository->find($data['userUpdate']));
        $type->setCreatedAtValue(new DateTime());
        $type->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($type);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $typeRepository->add($type, true);
        }

        return $this->responseData($type, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de type",
        description: "Permet de créer un type.",
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
    #[OA\Tag(name: 'type')]
    public function update(Request $request, Type $type, TypeRepository $typeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($type != null) {

                $type->setLibelle($data->libelle);
                $type->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $type->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($type);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $typeRepository->add($type, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($type, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) type.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) type',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Type::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'type')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Type $type, TypeRepository $villeRepository): Response
    {
        try {

            if ($type != null) {

                $villeRepository->remove($type, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($type);
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
     * Permet de supprimer plusieurs type.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Type::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'type')]
    public function deleteAll(Request $request, TypeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $type = $villeRepository->find($value['id']);

                if ($type != null) {
                    $villeRepository->remove($type);
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
