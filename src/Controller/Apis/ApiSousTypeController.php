<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\SousType;
use App\Repository\SousTypeRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/soustype')]
class ApiSousTypeController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister tous les sous-types",
        description: "Retourne la liste complète de tous les sous-types (données de référence)."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des sous-types récupérée avec succès",
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
                            new OA\Property(property: "libelle", type: "string", example: "Sous-type exemple"),
                            new OA\Property(property: "code", type: "string", example: "SOU-01"),
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
    #[OA\Tag(name: 'sous-type')]
    public function index(SousTypeRepository $sousTypeRepository): Response
    {
        try {
            $sousTypes = $sousTypeRepository->findAll();
            $response = $this->responseData($sousTypes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un sous-type par ID",
        description: "Retourne les détails d\'un sous-type à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du sous-type",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "SousType trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "libelle", type: "string", example: "Sous-type exemple"),
                        new OA\Property(property: "code", type: "string", example: "SOU-01"),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "SousType non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'sous-type')]
    public function getOne(?SousType $sousType)
    {
        try {
            if ($sousType) {
                $response = $this->response($sousType);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($sousType);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }
        return $response;
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouveau sous-type",
        description: "Crée un nouveau sous-type dans la liste de référence.",
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
        description: "SousType créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 5),
                        new OA\Property(property: "libelle", type: "string", example: "Sous-type exemple"),
                        new OA\Property(property: "code", type: "string", example: "SOU-01"),
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
    #[OA\Tag(name: 'sous-type')]
    public function create(Request $request, SousTypeRepository $sousTypeRepository): Response
    {
        $sousType = new SousType();
        $sousType->setLibelle($request->request->get('libelle'));
        $sousType->setCode($request->request->get('code'));
        $sousType->setCreatedBy($this->userRepository->find($request->request->get('userUpdate')));
        $sousType->setUpdatedBy($this->userRepository->find($request->request->get('userUpdate')));
        $sousType->setCreatedAtValue(new \DateTime());
        $sousType->setUpdatedAt(new \DateTime());

        $errorResponse = $this->errorResponse($sousType);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $sousTypeRepository->add($sousType, true);
        return $this->responseData($sousType, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier un sous-type existant",
        description: "Met à jour les informations d\'un sous-type identifié par son ID.",
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
        description: "Identifiant unique du sous-type à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "SousType modifié avec succès",
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
        description: "SousType non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'sous-type')]
    public function update(Request $request, SousType $sousType, SousTypeRepository $sousTypeRepository): Response
    {
        try {
            if ($sousType !== null) {
                $sousType->setLibelle($request->request->get('libelle'));
                $sousType->setCode($request->request->get('code'));
                $sousType->setUpdatedBy($this->userRepository->find($request->request->get('userUpdate')));
                $sousType->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($sousType);
                if ($errorResponse !== null) {
                    return $errorResponse;
                }
                $sousTypeRepository->add($sousType, true);
                return $this->responseData($sousType, 'group1', ['Content-Type' => 'application/json']);
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
        summary: "Supprimer un sous-type",
        description: "Supprime définitivement un sous-type à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du sous-type à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "SousType supprimé avec succès",
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
        description: "SousType non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'sous-type')]
    public function delete(Request $request, SousType $sousType, SousTypeRepository $repository): Response
    {
        try {
            if ($sousType != null) {
                $repository->remove($sousType, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($sousType);
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
        summary: "Supprimer plusieurs sous-types",
        description: "Supprime une liste de sous-types en passant leurs IDs dans le corps de la requête.",
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
        description: "SousTypes supprimés avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'sous-type')]
    public function deleteAll(Request $request, SousTypeRepository $repository): Response
    {
        try {
            foreach ($request->get('ids') as $key => $value) {
                $sousType = $repository->find($value['id']);
                if ($sousType != null) {
                    $repository->remove($sousType);
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
