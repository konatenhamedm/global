<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\SubstratDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Substrat;
use App\Repository\SubstratRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/substrat')]
class ApiSubstratController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des substrats.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Substrat::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'substrat')]
    // #[Security(name: 'Bearer')]
    public function index(SubstratRepository $substratRepository): Response
    {
        try {

            $substrats = $substratRepository->findAll();

          

            $response =  $this->responseData($substrats, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) substrat en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) substrat en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Substrat::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'substrat')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Substrat $substrat)
    {
        try {
            if ($substrat) {
                $response = $this->response($substrat);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($substrat);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) substrat.
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
    #[OA\Tag(name: 'substrat')]
    public function create(Request $request, SubstratRepository $substratRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $substrat = new Substrat();
        $substrat->setLibelle($request->get('libelle'));
        $substrat->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $substrat->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $substrat->setCreatedAtValue(new DateTime());
        $substrat->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($substrat);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $substratRepository->add($substrat, true);
        }

        return $this->responseData($substrat, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de substrat",
        description: "Permet de créer un substrat.",
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
    #[OA\Tag(name: 'substrat')]
    public function update(Request $request, Substrat $substrat, SubstratRepository $substratRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($substrat != null) {

                $substrat->setLibelle($request->get('libelle'));
                $substrat->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $substrat->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($substrat);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $substratRepository->add($substrat, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($substrat, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) substrat.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) substrat',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Substrat::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'substrat')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Substrat $substrat, SubstratRepository $villeRepository): Response
    {
        try {

            if ($substrat != null) {

                $villeRepository->remove($substrat, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($substrat);
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
     * Permet de supprimer plusieurs substrat.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Substrat::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'substrat')]
    public function deleteAll(Request $request, SubstratRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $substrat = $villeRepository->find($value['id']);

                if ($substrat != null) {
                    $villeRepository->remove($substrat);
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
