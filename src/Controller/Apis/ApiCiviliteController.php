<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CiviliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Civilite;
use App\Repository\CiviliteRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/civilite')]
class ApiCiviliteController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des civilites.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'civilite')]
    // #[Security(name: 'Bearer')]
    public function index(CiviliteRepository $civiliteRepository): Response
    {
        try {

            $civilites = $civiliteRepository->findAll();

          

            $response =  $this->responseData($civilites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) civilite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) civilite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'civilite')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Civilite $civilite)
    {
        try {
            if ($civilite) {
                $response = $this->response($civilite);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($civilite);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) civilite.
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
    #[OA\Tag(name: 'civilite')]
    public function create(Request $request, CiviliteRepository $civiliteRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $civilite = new Civilite();
        $civilite->setLibelle($request->get('libelle'));
        $civilite->setCode($request->get('code'));
        $civilite->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $civilite->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $civilite->setCreatedAtValue(new DateTime());
        $civilite->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($civilite);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $civiliteRepository->add($civilite, true);
        }

        return $this->responseData($civilite, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de civilite",
        description: "Permet de créer un civilite.",
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
    #[OA\Tag(name: 'civilite')]
    public function update(Request $request, Civilite $civilite, CiviliteRepository $civiliteRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($civilite != null) {

                $civilite->setLibelle($data->libelle);
                $civilite->setCode($data->code);
                $civilite->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $civilite->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($civilite);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $civiliteRepository->add($civilite, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($civilite, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) civilite.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) civilite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'civilite')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Civilite $civilite, CiviliteRepository $villeRepository): Response
    {
        try {

            if ($civilite != null) {

                $villeRepository->remove($civilite, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($civilite);
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
     * Permet de supprimer plusieurs civilite.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'civilite')]
    public function deleteAll(Request $request, CiviliteRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $civilite = $villeRepository->find($value['id']);

                if ($civilite != null) {
                    $villeRepository->remove($civilite);
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
