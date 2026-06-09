<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\FaceDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Face;
use App\Repository\FaceRepository;
use App\Repository\PanneauRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/face')]
class ApiFaceController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister toutes les faces",
        description: "Retourne la liste complète de toutes les faces de panneaux publicitaires."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des faces récupérée avec succès",
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
                            new OA\Property(property: "numFace", type: "integer", example: 1),
                            new OA\Property(property: "code", type: "string", example: "FACE-001"),
                            new OA\Property(property: "prix", type: "number", format: "float", example: 1500000),
                            new OA\Property(property: "etat", type: "string", enum: ["Libre", "Reserve", "Encours"], example: "Libre"),
                            new OA\Property(property: "panneau", type: "object", properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "code", type: "string", example: "PAN-001"),
                            ]),
                            new OA\Property(property: "imagePrincipale", type: "object", nullable: true, properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "path", type: "string", example: "media_deeps/photo.jpg"),
                                new OA\Property(property: "alt", type: "string", example: "Face 1"),
                            ]),
                            new OA\Property(property: "imageSecondaire1", type: "object", nullable: true),
                            new OA\Property(property: "imageSecondaire2", type: "object", nullable: true),
                            new OA\Property(property: "imageSecondaire3", type: "object", nullable: true),
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
    #[OA\Tag(name: 'face')]
    public function index(FaceRepository $faceRepository): Response
    {
        try {
            $faces = $faceRepository->findAll();
            $response =  $this->responseData($faces, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir une face par ID",
        description: "Retourne les détails complets d'une face de panneau à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de la face",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Face trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "numFace", type: "integer", example: 1),
                        new OA\Property(property: "code", type: "string", example: "FACE-001"),
                        new OA\Property(property: "prix", type: "number", format: "float", example: 1500000),
                        new OA\Property(property: "etat", type: "string", enum: ["Libre", "Reserve", "Encours"], example: "Libre"),
                        new OA\Property(property: "imagePrincipale", type: "object", nullable: true),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Face non trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'face')]
    public function getOne(?Face $face)
    {
        try {
            if ($face) {
                $response = $this->response($face);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($face);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer une nouvelle face de panneau",
        description: "Crée une face associée à un panneau existant. Supporte l'upload de 4 images (principale + 3 secondaires) en multipart/form-data.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['numFace', 'code', 'panneauId', 'prix', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "numFace", type: "integer", description: "Numéro de la face sur le panneau", example: 1),
                        new OA\Property(property: "code", type: "string", description: "Code unique de la face", example: "FACE-001"),
                        new OA\Property(property: "panneauId", type: "integer", description: "ID du panneau auquel rattacher cette face", example: 5),
                        new OA\Property(property: "prix", type: "number", description: "Prix de la face en FCFA", example: 1500000),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui crée", example: 1),
                        new OA\Property(property: "imagePrincipale", type: "string", format: "binary", description: "Photo principale de la face"),
                        new OA\Property(property: "imageSecondaire1", type: "string", format: "binary", description: "Photo secondaire 1 (optionnel)"),
                        new OA\Property(property: "imageSecondaire2", type: "string", format: "binary", description: "Photo secondaire 2 (optionnel)"),
                        new OA\Property(property: "imageSecondaire3", type: "string", format: "binary", description: "Photo secondaire 3 (optionnel)"),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Face créée avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 10),
                        new OA\Property(property: "numFace", type: "integer", example: 1),
                        new OA\Property(property: "code", type: "string", example: "FACE-001"),
                        new OA\Property(property: "prix", type: "number", example: 1500000),
                        new OA\Property(property: "etat", type: "string", example: "Libre"),
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
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le numéro de face est obligatoire."]),
            ]
        )
    )]
    #[OA\Tag(name: 'face')]
    public function create(Request $request, PanneauRepository $panneauRepository, FaceRepository $faceRepository): Response
    {
        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

        $face = new Face();
        $face->setNumFace($request->get('numFace'));
        $face->setCode($request->get("code"));
        $face->setPanneau($panneauRepository->find($request->get('panneauId')));

        $image1 = $request->files->get('imagePrincipale');
        $image2 = $request->files->get('imageSecondaire1');
        $image3 = $request->files->get('imageSecondaire2');
        $image4 = $request->files->get('imageSecondaire3');

        if ($image1) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image1, self::UPLOAD_PATH);
            if ($fichier) {
                $face->setImagePrincipale($fichier);
            }
        }
        if ($image2) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image2, self::UPLOAD_PATH);
            if ($fichier) {
                $face->setImageSecondaire1($fichier);
            }
        }
        if ($image3) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image3, self::UPLOAD_PATH);
            if ($fichier) {
                $face->setImageSecondaire2($fichier);
            }
        }
        if ($image4) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image4, self::UPLOAD_PATH);
            if ($fichier) {
                $face->setImageSecondaire3($fichier);
            }
        }

        $face->setPrix($request->get('prix'));
        $face->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $face->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $face->setCreatedAtValue(new DateTime());
        $face->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($face);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $faceRepository->add($face, true);
        }

        return $this->responseData($face, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier une face existante",
        description: "Met à jour les informations d'une face de panneau identifiée par son ID. Permet de remplacer les images.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "numFace", type: "integer", example: 1),
                        new OA\Property(property: "code", type: "string", example: "FACE-001"),
                        new OA\Property(property: "panneauId", type: "integer", description: "ID du panneau parent", example: 5),
                        new OA\Property(property: "prix", type: "number", description: "Nouveau prix de la face", example: 1800000),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui modifie", example: 1),
                        new OA\Property(property: "imagePrincipale", type: "string", format: "binary", description: "Nouvelle image principale (optionnel)"),
                        new OA\Property(property: "imageSecondaire1", type: "string", format: "binary", description: "Nouvelle image secondaire 1 (optionnel)"),
                        new OA\Property(property: "imageSecondaire2", type: "string", format: "binary", description: "Nouvelle image secondaire 2 (optionnel)"),
                        new OA\Property(property: "imageSecondaire3", type: "string", format: "binary", description: "Nouvelle image secondaire 3 (optionnel)"),
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
        description: "Identifiant unique de la face à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Face modifiée avec succès",
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
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le prix doit être un nombre positif."]),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Face non trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'face')]
    public function update(Request $request, Face $face, FaceRepository $faceRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($face != null) {

                $names = 'document_' . '01';
                $filePrefix  = str_slug($names);
                $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

                $face->setNumFace($request->get('numFace'));
                $face->setCode($request->get("code"));
                $face->setPanneau($request->get('panneau'));

                $image1 = $request->files->get('imagePrincipale');
                $image2 = $request->files->get('imageSecondaire1');
                $image3 = $request->files->get('imageSecondaire2');
                $image4 = $request->files->get('imageSecondaire3');

                if ($image1) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image1, self::UPLOAD_PATH);
                    if ($fichier) {
                        $face->setImagePrincipale($fichier);
                    }
                }
                if ($image2) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image2, self::UPLOAD_PATH);
                    if ($fichier) {
                        $face->setImageSecondaire1($fichier);
                    }
                }
                if ($image3) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image3, self::UPLOAD_PATH);
                    if ($fichier) {
                        $face->setImageSecondaire2($fichier);
                    }
                }
                if ($image4) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image4, self::UPLOAD_PATH);
                    if ($fichier) {
                        $face->setImageSecondaire3($fichier);
                    }
                }

                $face->setPrix($request->get('prix'));
                $face->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $face->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($face);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $faceRepository->add($face, true);
                }

                $response = $this->responseData($face, 'group1', ['Content-Type' => 'application/json']);
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


    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer une face",
        description: "Supprime définitivement une face de panneau à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de la face à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Face supprimée avec succès",
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
        description: "Face non trouvée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'face')]
    public function delete(Request $request, Face $face, FaceRepository $villeRepository): Response
    {
        try {
            if ($face != null) {
                $villeRepository->remove($face, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($face);
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
        summary: "Supprimer plusieurs faces",
        description: "Supprime une liste de faces en passant leurs IDs dans le corps de la requête.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ids",
                        type: "array",
                        description: "Liste des IDs faces à supprimer",
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
        description: "Faces supprimées avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'face')]
    public function deleteAll(Request $request, FaceRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $face = $villeRepository->find($value['id']);

                if ($face != null) {
                    $villeRepository->remove($face);
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
