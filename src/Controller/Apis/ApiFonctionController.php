<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\FonctionDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Fonction;
use App\Repository\FonctionRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/fonction')]
class ApiFonctionController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des fonctions.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fonction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'fonction')]
    // #[Security(name: 'Bearer')]
    public function index(FonctionRepository $fonctionRepository): Response
    {
        try {

            $fonctions = $fonctionRepository->findAll();

          

            $response =  $this->responseData($fonctions, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) fonction en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) fonction en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fonction::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'fonction')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Fonction $fonction)
    {
        try {
            if ($fonction) {
                $response = $this->response($fonction);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($fonction);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) fonction.
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
    #[OA\Tag(name: 'fonction')]
    public function create(Request $request, FonctionRepository $fonctionRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $fonction = new Fonction();
        $fonction->setLibelle($request->get('libelle'));
        $fonction->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $fonction->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $fonction->setCreatedAtValue(new DateTime());
        $fonction->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($fonction);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $fonctionRepository->add($fonction, true);
        }

        return $this->responseData($fonction, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de fonction",
        description: "Permet de créer un fonction.",
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
    #[OA\Tag(name: 'fonction')]
    public function update(Request $request, Fonction $fonction, FonctionRepository $fonctionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($fonction != null) {

                $fonction->setLibelle($data->libelle);
                $fonction->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $fonction->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($fonction);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $fonctionRepository->add($fonction, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($fonction, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) fonction.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) fonction',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fonction::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'fonction')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Fonction $fonction, FonctionRepository $villeRepository): Response
    {
        try {

            if ($fonction != null) {

                $villeRepository->remove($fonction, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($fonction);
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
     * Permet de supprimer plusieurs fonction.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fonction::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'fonction')]
    public function deleteAll(Request $request, FonctionRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $fonction = $villeRepository->find($value['id']);

                if ($fonction != null) {
                    $villeRepository->remove($fonction);
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
