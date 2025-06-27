<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TypeClientDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\TypeClient;
use App\Repository\TypeClientRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/typeClient')]
class ApiTypeClientController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typeClients.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeClient::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeClient')]
    // #[Security(name: 'Bearer')]
    public function index(TypeClientRepository $typeClientRepository): Response
    {
        try {

            $typeClients = $typeClientRepository->findAll();

          

            $response =  $this->responseData($typeClients, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typeClient en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeClient en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeClient::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'typeClient')]
    //#[Security(name: 'Bearer')]
    public function getOne(?TypeClient $typeClient)
    {
        try {
            if ($typeClient) {
                $response = $this->response($typeClient);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typeClient);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) typeClient.
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
    #[OA\Tag(name: 'typeClient')]
    public function create(Request $request, TypeClientRepository $typeClientRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $typeClient = new TypeClient();
        $typeClient->setLibelle($request->get('libelle'));
        $typeClient->setCode($request->get('code'));
        $typeClient->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $typeClient->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $typeClient->setCreatedAtValue(new DateTime());
        $typeClient->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($typeClient);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $typeClientRepository->add($typeClient, true);
        }

        return $this->responseData($typeClient, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de typeClient",
        description: "Permet de créer un typeClient.",
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
    #[OA\Tag(name: 'typeClient')]
    public function update(Request $request, TypeClient $typeClient, TypeClientRepository $typeClientRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typeClient != null) {

                $typeClient->setLibelle($request->get('libelle'));
                $typeClient->setCode($request->get('code'));
                $typeClient->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $typeClient->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($typeClient);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $typeClientRepository->add($typeClient, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($typeClient, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typeClient.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typeClient',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeClient::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'typeClient')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, TypeClient $typeClient, TypeClientRepository $villeRepository): Response
    {
        try {

            if ($typeClient != null) {

                $villeRepository->remove($typeClient, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typeClient);
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
     * Permet de supprimer plusieurs typeClient.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeClient::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'typeClient')]
    public function deleteAll(Request $request, TypeClientRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $typeClient = $villeRepository->find($value['id']);

                if ($typeClient != null) {
                    $villeRepository->remove($typeClient);
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
