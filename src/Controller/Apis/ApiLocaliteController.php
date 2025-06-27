<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\LocaliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Localite;
use App\Repository\LocaliteRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/localite')]
class ApiLocaliteController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des localites.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Localite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'localite')]
    // #[Security(name: 'Bearer')]
    public function index(LocaliteRepository $localiteRepository): Response
    {
        try {

            $localites = $localiteRepository->findAll();

          

            $response =  $this->responseData($localites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) localite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) localite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Localite::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'localite')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Localite $localite)
    {
        try {
            if ($localite) {
                $response = $this->response($localite);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($localite);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) localite.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    //new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'localite')]
    public function create(Request $request, LocaliteRepository $localiteRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $localite = new Localite();
        $localite->setLibelle($data['libelle']);
        $localite->setCreatedBy($this->userRepository->find($data['userUpdate']));
        $localite->setUpdatedBy($this->userRepository->find($data['userUpdate']));
        $localite->setCreatedAtValue(new DateTime());
        $localite->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($localite);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $localiteRepository->add($localite, true);
        }

        return $this->responseData($localite, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de localite",
        description: "Permet de créer un localite.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    //new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'localite')]
    public function update(Request $request, Localite $localite, LocaliteRepository $localiteRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($localite != null) {

                $localite->setLibelle($data->libelle);
                $localite->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $localite->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($localite);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $localiteRepository->add($localite, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($localite, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) localite.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) localite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Localite::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'localite')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Localite $localite, LocaliteRepository $villeRepository): Response
    {
        try {

            if ($localite != null) {

                $villeRepository->remove($localite, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($localite);
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
     * Permet de supprimer plusieurs localite.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Localite::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'localite')]
    public function deleteAll(Request $request, LocaliteRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $localite = $villeRepository->find($value['id']);

                if ($localite != null) {
                    $villeRepository->remove($localite);
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
