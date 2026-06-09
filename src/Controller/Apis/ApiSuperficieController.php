<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Superficie;
use App\Repository\SuperficieRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/superficie')]
class ApiSuperficieController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister tous les superficies",
        description: "Retourne la liste complète de tous les superficies (données de référence)."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des superficies récupérée avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "libelle", type: "string", example: "Superficie exemple"),
                            new OA\Property(property: "code", type: "string", example: "SUP-01"),
                        ]
                    )
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur interne du serveur",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 500),
                new OA\Property(property: "message", type: "string", example: "Erreur interne du serveur"),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Tag(name: 'superficie')]
    public function index(SuperficieRepository $superficieRepository): Response
    {
        try {
            $superficies = $superficieRepository->findAll();
            $response = $this->responseData($superficies, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un superficie par ID",
        description: "Retourne les détails d\'un superficie à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du superficie",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Superficie trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "libelle", type: "string", example: "Superficie exemple"),
                        new OA\Property(property: "code", type: "string", example: "SUP-01"),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Superficie non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'superficie')]
    public function getOne(?Superficie $superficie)
    {
        try {
            if ($superficie) {
                $response = $this->response($superficie);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($superficie);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }
        return $response;
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouveau superficie",
        description: "Crée un nouveau superficie dans la liste de référence.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["libelle", "code", "userUpdate"],
                    properties: [
                        new OA\Property(property: "libelle", type: "string", example: "M."),
                        new OA\Property(property: "code", type: "string", example: "CODE-01"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l\'utilisateur qui crée", example: 1),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Superficie créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 5),
                        new OA\Property(property: "libelle", type: "string", example: "Superficie exemple"),
                        new OA\Property(property: "code", type: "string", example: "SUP-01"),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Données invalides",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 400),
                new OA\Property(property: "message", type: "string", example: "Validation failed"),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le libellé est obligatoire."]),
            ]
        )
    )]
    #[OA\Tag(name: 'superficie')]
    public function create(Request $request, SuperficieRepository $superficieRepository): Response
    {
        $superficie = new Superficie();
        $superficie->setLibelle($request->request->get('libelle'));
        $superficie->setCode($request->request->get('code'));
        $superficie->setCreatedBy($this->userRepository->find($request->request->get('userUpdate')));
        $superficie->setUpdatedBy($this->userRepository->find($request->request->get('userUpdate')));
        $superficie->setCreatedAtValue(new \DateTime());
        $superficie->setUpdatedAt(new \DateTime());

        $errorResponse = $this->errorResponse($superficie);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $superficieRepository->add($superficie, true);
        return $this->responseData($superficie, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier un superficie existant",
        description: "Met à jour les informations d\'un superficie identifié par son ID.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "libelle", type: "string", example: "M."),
                        new OA\Property(property: "code", type: "string", example: "CODE-01"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l\'utilisateur qui modifie", example: 1),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du superficie à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Superficie modifié avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(property: "data", type: "object"),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Données invalides",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 400),
                new OA\Property(property: "message", type: "string", example: "Validation failed"),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le libellé est obligatoire."]),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Superficie non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'superficie')]
    public function update(Request $request, Superficie $superficie, SuperficieRepository $superficieRepository): Response
    {
        try {
            if ($superficie !== null) {
                $superficie->setLibelle($request->request->get('libelle'));
                $superficie->setCode($request->request->get('code'));
                $superficie->setUpdatedBy($this->userRepository->find($request->request->get('userUpdate')));
                $superficie->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($superficie);
                if ($errorResponse !== null) {
                    return $errorResponse;
                }
                $superficieRepository->add($superficie, true);
                return $this->responseData($superficie, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(300);
                return $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            return $this->response('[]');
        }
    }


    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer un superficie",
        description: "Supprime définitivement un superficie à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du superficie à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Superficie supprimé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Superficie non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'superficie')]
    public function delete(Request $request, Superficie $superficie, SuperficieRepository $repository): Response
    {
        try {
            if ($superficie != null) {
                $repository->remove($superficie, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($superficie);
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

    #[Route('/delete/all', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer plusieurs superficies",
        description: "Supprime une liste de superficies en passant leurs IDs dans le corps de la requête.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ids",
                        type: "array",
                        description: "Liste des IDs à supprimer",
                        items: new OA\Items(
                            type: "object",
                            properties: [new OA\Property(property: "id", type: "integer", example: 1)]
                        ),
                        example: [["id" => 1], ["id" => 2]]
                    ),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Superficies supprimés avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'superficie')]
    public function deleteAll(Request $request, SuperficieRepository $repository): Response
    {
        try {
            foreach ($request->get('ids') as $key => $value) {
                $superficie = $repository->find($value['id']);
                if ($superficie != null) {
                    $repository->remove($superficie);
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
