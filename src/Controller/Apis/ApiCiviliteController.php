<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Civilite;
use App\Repository\CiviliteRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/civilite')]
class ApiCiviliteController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister tous les civilités",
        description: "Retourne la liste complète de tous les civilités (données de référence)."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des civilités récupérée avec succès",
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
                            new OA\Property(property: "libelle", type: "string", example: "Civilité exemple"),
                            new OA\Property(property: "code", type: "string", example: "CIV-01"),
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
    #[OA\Tag(name: 'civilite')]
    public function index(CiviliteRepository $civiliteRepository): Response
    {
        try {
            $civilites = $civiliteRepository->findAll();
            $response = $this->responseData($civilites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un civilité par ID",
        description: "Retourne les détails d\'un civilité à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du civilité",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Civilite trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "libelle", type: "string", example: "Civilité exemple"),
                        new OA\Property(property: "code", type: "string", example: "CIV-01"),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Civilite non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'civilite')]
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


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouveau civilité",
        description: "Crée un nouveau civilité dans la liste de référence.",
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
        description: "Civilite créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 5),
                        new OA\Property(property: "libelle", type: "string", example: "Civilité exemple"),
                        new OA\Property(property: "code", type: "string", example: "CIV-01"),
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
    #[OA\Tag(name: 'civilite')]
    public function create(Request $request, CiviliteRepository $civiliteRepository): Response
    {
        $civilite = new Civilite();
        $civilite->setLibelle($request->request->get('libelle'));
        $civilite->setCode($request->request->get('code'));
        $civilite->setCreatedBy($this->userRepository->find($request->request->get('userUpdate')));
        $civilite->setUpdatedBy($this->userRepository->find($request->request->get('userUpdate')));
        $civilite->setCreatedAtValue(new \DateTime());
        $civilite->setUpdatedAt(new \DateTime());

        $errorResponse = $this->errorResponse($civilite);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $civiliteRepository->add($civilite, true);
        return $this->responseData($civilite, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier un civilité existant",
        description: "Met à jour les informations d\'un civilité identifié par son ID.",
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
        description: "Identifiant unique du civilité à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Civilite modifié avec succès",
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
        description: "Civilite non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'civilite')]
    public function update(Request $request, Civilite $civilite, CiviliteRepository $civiliteRepository): Response
    {
        try {
            if ($civilite !== null) {
                $civilite->setLibelle($request->request->get('libelle'));
                $civilite->setCode($request->request->get('code'));
                $civilite->setUpdatedBy($this->userRepository->find($request->request->get('userUpdate')));
                $civilite->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($civilite);
                if ($errorResponse !== null) {
                    return $errorResponse;
                }
                $civiliteRepository->add($civilite, true);
                return $this->responseData($civilite, 'group1', ['Content-Type' => 'application/json']);
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
        summary: "Supprimer un civilité",
        description: "Supprime définitivement un civilité à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du civilité à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Civilite supprimé avec succès",
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
        description: "Civilite non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'civilite')]
    public function delete(Request $request, Civilite $civilite, CiviliteRepository $repository): Response
    {
        try {
            if ($civilite != null) {
                $repository->remove($civilite, true);
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

    #[Route('/delete/all', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer plusieurs civilités",
        description: "Supprime une liste de civilités en passant leurs IDs dans le corps de la requête.",
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
        description: "Civilites supprimés avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'civilite')]
    public function deleteAll(Request $request, CiviliteRepository $repository): Response
    {
        try {
            foreach ($request->get('ids') as $key => $value) {
                $civilite = $repository->find($value['id']);
                if ($civilite != null) {
                    $repository->remove($civilite);
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
