<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\SpecificationDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Specification;
use App\Repository\SpecificationRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/specification')]
class ApiSpecificationController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des specifications.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'specification')]
    // #[Security(name: 'Bearer')]
    public function index(SpecificationRepository $specificationRepository): Response
    {
        try {

            $specifications = $specificationRepository->findAll();

          

            $response =  $this->responseData($specifications, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) specification en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) specification en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specification::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'specification')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Specification $specification)
    {
        try {
            if ($specification) {
                $response = $this->response($specification);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($specification);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) specification.
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
    #[OA\Tag(name: 'specification')]
    public function create(Request $request, SpecificationRepository $specificationRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $specification = new Specification();
        $specification->setLibelle($request->get('libelle'));
        $specification->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $specification->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $specification->setCreatedAtValue(new DateTime());
        $specification->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($specification);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $specificationRepository->add($specification, true);
        }

        return $this->responseData($specification, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de specification",
        description: "Permet de créer un specification.",
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
    #[OA\Tag(name: 'specification')]
    public function update(Request $request, Specification $specification, SpecificationRepository $specificationRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($specification != null) {

                $specification->setLibelle($request->get('libelle'));
                $specification->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $specification->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($specification);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $specificationRepository->add($specification, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($specification, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) specification.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) specification',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specification::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'specification')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Specification $specification, SpecificationRepository $villeRepository): Response
    {
        try {

            if ($specification != null) {

                $villeRepository->remove($specification, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($specification);
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
     * Permet de supprimer plusieurs specification.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specification::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'specification')]
    public function deleteAll(Request $request, SpecificationRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $specification = $villeRepository->find($value['id']);

                if ($specification != null) {
                    $villeRepository->remove($specification);
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
