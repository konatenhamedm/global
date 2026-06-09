<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\PanneauDTO;
use App\Entity\Face;
use App\Entity\Ligne;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Panneau;
use App\Entity\SousType;
use App\Entity\Substrat;
use App\Repository\IlluminationRepository;
use App\Repository\LigneRepository;
use App\Repository\LocaliteRepository;
use App\Repository\OrientationRepository;
use App\Repository\PanneauRepository;
use App\Repository\SousTypeRepository;
use App\Repository\SpecificationRepository;
use App\Repository\SubstratRepository;
use App\Repository\SuperficieRepository;
use App\Repository\TailleRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\Utils;
use DateTime;
use Doctrine\DBAL\Types\TypeRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/panneau')]
class ApiPanneauController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister tous les panneaux",
        description: "Retourne la liste complète de tous les panneaux publicitaires enregistrés dans le système."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des panneaux récupérée avec succès",
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
                            new OA\Property(property: "code", type: "string", example: "PAN-001"),
                            new OA\Property(property: "gpsLat", type: "string", example: "5.3600"),
                            new OA\Property(property: "gpsLong", type: "string", example: "-4.0083"),
                            new OA\Property(property: "zone", type: "string", example: "Zone Nord"),
                            new OA\Property(property: "localisation", type: "string", example: "Abidjan, Cocody"),
                            new OA\Property(property: "type", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "sousType", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "taille", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "dimension", type: "string")]),
                            new OA\Property(property: "localite", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "faces", type: "array", items: new OA\Items(type: "object")),
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
    #[OA\Tag(name: 'panneau')]
    public function index(PanneauRepository $panneauRepository): Response
    {
        try {
            $panneaus = $panneauRepository->findAll();
            $response =  $this->responseData($panneaus, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/panneau/utilises', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister les panneaux avec l'état de leurs faces et réservations en cours",
        description: "Retourne la liste des panneaux avec pour chaque face : son état, son prix, ses images et les périodes de réservation actives. Utile pour afficher la disponibilité sur le calendrier."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des panneaux avec état des faces",
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
                            new OA\Property(property: "code", type: "string", example: "PAN-001"),
                            new OA\Property(property: "gpsLong", type: "string", example: "-4.0083"),
                            new OA\Property(property: "gpsLat", type: "string", example: "5.3600"),
                            new OA\Property(property: "type", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "sousType", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "taille", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string", description: "Dimension ex: 4x3m")]),
                            new OA\Property(property: "localite", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "superficie", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "orientation", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "substrat", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "specification", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                            new OA\Property(property: "zone", type: "string", example: "Zone Nord"),
                            new OA\Property(property: "localisation", type: "string", example: "Abidjan, Cocody"),
                            new OA\Property(
                                property: "faces",
                                type: "array",
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 5),
                                        new OA\Property(property: "numFace", type: "integer", example: 1),
                                        new OA\Property(property: "etat", type: "string", enum: ["Libre", "Reserve", "Encours"], example: "Libre"),
                                        new OA\Property(property: "code", type: "string", example: "FACE-001"),
                                        new OA\Property(property: "prix", type: "number", format: "float", example: 1500000),
                                        new OA\Property(
                                            property: "imagePrincipale",
                                            type: "object",
                                            properties: [
                                                new OA\Property(property: "alt", type: "string", example: "Panneau face 1"),
                                                new OA\Property(property: "path", type: "string", example: "media_deeps/image.jpg"),
                                            ]
                                        ),
                                        new OA\Property(
                                            property: "allReservationsEnCours",
                                            type: "array",
                                            description: "Périodes de réservation actives sur cette face",
                                            items: new OA\Items(
                                                properties: [
                                                    new OA\Property(property: "dateDebut", type: "string", format: "date", example: "2025-01-15"),
                                                    new OA\Property(property: "dateFin", type: "string", format: "date", example: "2025-03-15"),
                                                ]
                                            )
                                        ),
                                    ]
                                )
                            ),
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
    #[OA\Tag(name: 'panneau')]
    public function listePanneauxLibre(
        PanneauRepository $panneauRepository,
        LigneRepository $ligneRepository
    ): Response {
        try {
            $panneaux = $panneauRepository->findAll();

            $data = array_map(function (Panneau $panneau) use ($ligneRepository) {
                return [
                    'id' => $panneau->getId(),
                    'code' => $panneau->getCode(),
                    'gpsLong' => $panneau->getGpsLong(),
                    'gpsLat' => $panneau->getGpsLat(),
                    'type' => [
                        'id' => $panneau->getType()->getId(),
                        'libelle' => $panneau->getType()->getLibelle(),
                    ],
                    'sousType' => [
                        'id' => $panneau->getSousType()->getId(),
                        'libelle' => $panneau->getSousType()->getLibelle(),
                    ],
                    'taille' => [
                        'id' => $panneau->getTaille()->getId(),
                        'libelle' => $panneau->getTaille()->getDimension(),
                    ],
                    'localite' => [
                        'id' => $panneau->getLocalite()->getId(),
                        'libelle' => $panneau->getLocalite()->getLibelle(),
                    ],
                    'superficie' => [
                        'id' => $panneau->getSuperficie()->getId(),
                        'libelle' => $panneau->getSuperficie()->getLibelle(),
                    ],
                    'orientation' => [
                        'id' => $panneau->getOrientation()->getId(),
                        'libelle' => $panneau->getOrientation()->getLibelle(),
                    ],
                    'substrat' => [
                        'id' => $panneau->getSubstrat()->getId(),
                        'libelle' => $panneau->getSubstrat()->getLibelle(),
                    ],
                    'faces' => array_map(function (Face $face) use ($ligneRepository) {
                        $activeLignes = $ligneRepository->findActiveByFace($face->getId());

                        return [
                            'id' => $face->getId(),
                            'numFace' => $face->getNumFace(),
                            'etat' => $face->getEtat(),
                            'code' => $face->getCode(),
                            'prix' => $face->getPrix(),
                            'imagePrincipale' => [
                                'alt' => $face->getImagePrincipale() ? $face->getImagePrincipale()->getAlt() : '',
                                'path' => $face->getImagePrincipale() ? $face->getImagePrincipale()->getPath() : '',
                            ],
                            'allReservationsEnCours' => array_map(function (Ligne $ligne) {
                                return [
                                    'dateDebut' => $ligne->getDateDebut()->format('Y-m-d'),
                                    'dateFin'   => $ligne->getDateFin()->format('Y-m-d'),
                                ];
                            }, $activeLignes),
                        ];
                    }, $panneau->getFaces()->toArray()),
                    'specification' => [
                        'id' => $panneau->getSpecification()->getId(),
                        'libelle' => $panneau->getSpecification()->getLibelle(),
                    ],
                    'zone' => $panneau->getZone(),
                    'localisation' => $panneau->getLocalisation(),
                ];
            }, $panneaux);

            $response = $this->responseData($data, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un panneau par ID",
        description: "Retourne les détails complets d'un panneau à partir de son identifiant numérique."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du panneau",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Panneau trouvé",
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
        response: 300,
        description: "Panneau non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'panneau')]
    public function getOne(?Panneau $panneau)
    {
        try {
            if ($panneau) {
                $response = $this->response($panneau);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($panneau);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/get/one/by/code/{code}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un panneau par code",
        description: "Retourne les détails complets d'un panneau à partir de son code alphanumérique unique (ex: PAN-001)."
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'path',
        required: true,
        description: "Code alphanumérique unique du panneau",
        schema: new OA\Schema(type: 'string', example: 'PAN-001')
    )]
    #[OA\Response(
        response: 200,
        description: "Panneau trouvé",
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
        response: 300,
        description: "Panneau non trouvé pour ce code",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "data", type: "array", example: []),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Tag(name: 'panneau')]
    public function getOneByCode(PanneauRepository $panneauRepository, $code)
    {
        try {
            $panneau = $panneauRepository->findOneBy(['code' => $code]);
            if ($panneau) {
                $response =  $this->responseData($panneau, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouveau panneau avec ses faces",
        description: "Crée un panneau publicitaire complet avec ses faces associées. Chaque face peut avoir jusqu'à 4 images (principale + 3 secondaires). Les faces sont envoyées sous forme de tableau `lignes[]` avec les fichiers images correspondants.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['gpsLat', 'gpsLong', 'type', 'localite', 'taille', 'superficie', 'orientation', 'substrat', 'specification', 'code', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "gpsLat", type: "string", description: "Latitude GPS", example: "5.3600"),
                        new OA\Property(property: "gpsLong", type: "string", description: "Longitude GPS", example: "-4.0083"),
                        new OA\Property(property: "type", type: "integer", description: "ID du type de panneau", example: 1),
                        new OA\Property(property: "illumination", type: "integer", description: "ID de l'illumination", example: 2),
                        new OA\Property(property: "specification", type: "integer", description: "ID de la spécification", example: 1),
                        new OA\Property(property: "soustype", type: "integer", description: "ID du sous-type", example: 3),
                        new OA\Property(property: "substrat", type: "integer", description: "ID du substrat", example: 1),
                        new OA\Property(property: "localite", type: "integer", description: "ID de la localité", example: 4),
                        new OA\Property(property: "taille", type: "integer", description: "ID de la taille", example: 2),
                        new OA\Property(property: "superficie", type: "integer", description: "ID de la superficie", example: 1),
                        new OA\Property(property: "orientation", type: "integer", description: "ID de l'orientation", example: 3),
                        new OA\Property(property: "localisation", type: "string", description: "Description de la localisation", example: "Abidjan, Cocody Riviera 2"),
                        new OA\Property(property: "zone", type: "string", description: "Zone géographique", example: "Zone A"),
                        new OA\Property(property: "code", type: "string", description: "Code unique du panneau", example: "PAN-001"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui crée", example: 1),
                        new OA\Property(
                            property: "lignes",
                            type: "array",
                            description: "Liste des faces du panneau",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "imagePrincipale", type: "string", format: "binary", description: "Photo principale de la face"),
                                    new OA\Property(property: "imageSecondaire1", type: "string", format: "binary", description: "Photo secondaire 1"),
                                    new OA\Property(property: "imageSecondaire2", type: "string", format: "binary", description: "Photo secondaire 2"),
                                    new OA\Property(property: "imageSecondaire3", type: "string", format: "binary", description: "Photo secondaire 3"),
                                    new OA\Property(property: "prix", type: "number", description: "Prix de la face en FCFA", example: 1500000),
                                    new OA\Property(property: "numFace", type: "integer", description: "Numéro de la face", example: 1),
                                    new OA\Property(property: "code", type: "string", description: "Code de la face", example: "FACE-001"),
                                ]
                            ),
                        ),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Panneau créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 10),
                        new OA\Property(property: "code", type: "string", example: "PAN-001"),
                        new OA\Property(property: "gpsLat", type: "string", example: "5.3600"),
                        new OA\Property(property: "gpsLong", type: "string", example: "-4.0083"),
                        new OA\Property(property: "faces", type: "array", items: new OA\Items(type: "object")),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Données invalides ou champs manquants",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 400),
                new OA\Property(property: "message", type: "string", example: "Validation failed"),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le code est obligatoire."]),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Erreur lors de la création (ex: type ou localité invalide)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 400),
                new OA\Property(property: "message", type: "string", example: "Requête invalide"),
                new OA\Property(property: "errors", type: "object", properties: [
                    new OA\Property(property: "message", type: "string", example: "Une erreur est survenue lors de la création du panneau."),
                    new OA\Property(property: "error", type: "string", example: "Call to a member function getId() on null"),
                ]),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Tag(name: 'panneau')]
    public function create(
        Request $request,
        PanneauRepository $panneauRepository,
        TypeRepository $typeRepository,
        IlluminationRepository $illuminationRepository,
        SousTypeRepository $sousTypeRepository,
        SubstratRepository $substratRepository,
        LocaliteRepository $localiteRepository,
        TailleRepository $tailleRepository,
        SuperficieRepository $superficieRepository,
        OrientationRepository $orientationRepository,
        UserRepository $userRepository,
        SpecificationRepository $specificationRepository,
        Utils $utils,
    ): Response {
        try {
            $names = 'document_' . '01';
            $filePrefix  = str_slug($names);
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            $data = json_decode($request->getContent(), true);

            $panneau = new Panneau();

            $panneau->setGpsLat($request->get('gpsLat'));
            $panneau->setGpsLong($request->get('gpsLong'));

            $panneau->setSpecification($specificationRepository->find($request->get('specification')));
            $panneau->setType($typeRepository->find($request->get('type')));

            $panneau->setIllumination($illuminationRepository->find($request->get('illumination')));
            $panneau->setSousType($sousTypeRepository->find($request->get('soustype')));
            $panneau->setSubstrat($substratRepository->find($request->get('substrat')));
            $panneau->setLocalite($localiteRepository->find($request->get('localite')));
            $panneau->setTaille($tailleRepository->find($request->get('taille')));
            $panneau->setSuperficie($superficieRepository->find($request->get('superficie')));
            $panneau->setOrientation($orientationRepository->find($request->get('orientation')));
            $panneau->setCode($request->get('code'));
            $panneau->setZone($request->get('zone'));
            $panneau->setLocalisation($request->get('localisation'));
            $panneau->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
            $panneau->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
            $panneau->setCreatedAtValue(new DateTime());
            $panneau->setUpdatedAt(new DateTime());

            $facesData = $request->get('lignes');
            $uploadedFiles = $request->files->get('lignes');

            foreach ($facesData as $index => $faceData) {

                $newFace = new Face();
                $newFace
                    ->setNumFace($faceData['numFace'])
                    ->setCode($faceData['code'])
                    ->setPrix($faceData['prix']);

                if (isset($uploadedFiles[$index])) {
                    $fileKeys = [
                        'imagePrincipale',
                        'imageSecondaire1',
                        'imageSecondaire2',
                        'imageSecondaire3',
                    ];

                    foreach ($fileKeys as $key) {
                        if (!empty($uploadedFiles[$index][$key])) {
                            $uploadedFile = $uploadedFiles[$index][$key];
                            $fichier = $utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                            if ($fichier) {
                                $setter = 'set' . ucfirst($key);
                                $newFace->$setter($fichier);
                            }
                        }
                    }
                }

                $user = $this->userRepository->find($request->get('userUpdate'));

                $newFace->setCreatedBy($user);
                $newFace->setUpdatedBy($user);
                $newFace->setCreatedAtValue(new \DateTime());
                $newFace->setUpdatedAt(new \DateTime());

                $panneau->addFace($newFace);
            }

            $errorResponse = $this->errorResponse($panneau);
            if ($errorResponse !== null) {
                return $errorResponse;
            } else {
                $panneauRepository->add($panneau, true);
            }
        } catch (\Throwable $th) {
            return $this->respondBadRequest(
                [
                    'message' => 'Une erreur est survenue lors de la création du panneau.',
                    'error' => $th->getMessage()
                ]
            );
        }

        return $this->responseData($panneau, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier un panneau existant",
        description: "Met à jour les informations d'un panneau publicitaire identifié par son ID. Ne modifie pas les faces associées (utilisez /api/face/update/{id} pour cela).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "gpslat", type: "string", description: "Latitude GPS", example: "5.3600"),
                        new OA\Property(property: "gpslong", type: "string", description: "Longitude GPS", example: "-4.0083"),
                        new OA\Property(property: "type", type: "integer", description: "ID du type de panneau", example: 1),
                        new OA\Property(property: "specification", type: "integer", description: "ID de la spécification", example: 1),
                        new OA\Property(property: "illumination", type: "integer", description: "ID de l'illumination", example: 2),
                        new OA\Property(property: "soustype", type: "integer", description: "ID du sous-type", example: 3),
                        new OA\Property(property: "substrat", type: "integer", description: "ID du substrat", example: 1),
                        new OA\Property(property: "localite", type: "integer", description: "ID de la localité", example: 4),
                        new OA\Property(property: "taille", type: "integer", description: "ID de la taille", example: 2),
                        new OA\Property(property: "superficie", type: "integer", description: "ID de la superficie", example: 1),
                        new OA\Property(property: "orientation", type: "integer", description: "ID de l'orientation", example: 3),
                        new OA\Property(property: "code", type: "string", description: "Code du panneau", example: "PAN-001"),
                        new OA\Property(property: "zone", type: "string", example: "Zone A"),
                        new OA\Property(property: "localisation", type: "string", example: "Abidjan, Cocody"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui effectue la modification", example: 1),
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
        description: "Identifiant unique du panneau à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Panneau modifié avec succès",
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
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le code est obligatoire."]),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Panneau non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'panneau')]
    public function update(
        Request $request,
        Panneau $panneau,
        PanneauRepository $panneauRepository,
        TypeRepository $typeRepository,
        IlluminationRepository $illuminationRepository,
        SousTypeRepository $sousTypeRepository,
        SubstratRepository $substratRepository,
        LocaliteRepository $localiteRepository,
        TailleRepository $tailleRepository,
        SuperficieRepository $superficieRepository,
        OrientationRepository $orientationRepository,
        SpecificationRepository $specificationRepository,
        UserRepository $userRepository
    ): Response {
        try {
            $data = json_decode($request->getContent());
            if ($panneau != null) {

                $panneau->setCode($request->get('code'));
                $panneau->setGpsLat($request->get('gpslat'));
                $panneau->setGpsLong($request->get('gpslong'));
                $panneau->setSpecification($specificationRepository->find($request->get('specification')));
                $panneau->setType($typeRepository->find($request->get('type')));
                $panneau->setIllumination($illuminationRepository->find($request->get('illumination')));
                $panneau->setSousType($sousTypeRepository->find($request->get('soustype')));
                $panneau->setSubstrat($substratRepository->find($request->get('substrat')));
                $panneau->setLocalite($localiteRepository->find($request->get('localite')));
                $panneau->setTaille($tailleRepository->find($request->get('taille')));
                $panneau->setSuperficie($superficieRepository->find($request->get('superficie')));
                $panneau->setOrientation($orientationRepository->find($request->get('orientation')));
                $panneau->setZone($request->get('zone'));
                $panneau->setLocalisation($request->get('localisation'));

                $panneau->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $panneau->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($panneau);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $panneauRepository->add($panneau, true);
                }

                $response = $this->responseData($panneau, 'group1', ['Content-Type' => 'application/json']);
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
        summary: "Supprimer un panneau",
        description: "Supprime définitivement un panneau et ses faces associées à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du panneau à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Panneau supprimé avec succès",
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
        description: "Panneau non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'panneau')]
    public function delete(Request $request, Panneau $panneau, PanneauRepository $villeRepository): Response
    {
        try {
            if ($panneau != null) {
                $villeRepository->remove($panneau, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($panneau);
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
        summary: "Supprimer plusieurs panneaux",
        description: "Supprime une liste de panneaux en passant leurs IDs dans le corps de la requête.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ids",
                        type: "array",
                        description: "Liste des IDs panneaux à supprimer",
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
        description: "Panneaux supprimés avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'panneau')]
    public function deleteAll(Request $request, PanneauRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $panneau = $villeRepository->find($value['id']);

                if ($panneau != null) {
                    $villeRepository->remove($panneau);
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
