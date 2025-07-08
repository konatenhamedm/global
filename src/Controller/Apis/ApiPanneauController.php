<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\PanneauDTO;
use App\Entity\Face;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Panneau;
use App\Entity\SousType;
use App\Entity\Substrat;
use App\Repository\IlluminationRepository;
use App\Repository\LocaliteRepository;
use App\Repository\OrientationRepository;
use App\Repository\PanneauRepository;
use App\Repository\SousTypeRepository;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/panneau')]
class ApiPanneauController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des panneaus.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Panneau::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'panneau')]
    // #[Security(name: 'Bearer')]
    public function index(PanneauRepository $panneauRepository): Response
    {
        try {

            $panneaus = $panneauRepository->findAll();



            $response =  $this->responseData($panneaus, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) panneau en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) panneau en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Panneau::class, groups: ['full']))

        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'panneau')]
    //#[Security(name: 'Bearer')]
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


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) panneau.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "gpslat", type: "string"),
                        new OA\Property(property: "gpslong", type: "string"),
                        new OA\Property(property: "type", type: "string"),
                        new OA\Property(property: "illumination", type: "string"),
                        new OA\Property(property: "soustype", type: "string"),
                        new OA\Property(property: "substrat", type: "string"),
                        new OA\Property(property: "localite", type: "string"),
                        new OA\Property(property: "taille", type: "string"),
                        new OA\Property(property: "superficie", type: "string"),
                        new OA\Property(property: "orientation", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),

                        new OA\Property(
                            property: "lignes",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "imagePrincipale", type: "string", format: "binary"), //photo
                                    new OA\Property(property: "imageSecondaire1", type: "string", format: "binary"), //photo
                                    new OA\Property(property: "imageSecondaire2", type: "string", format: "binary"), //photo
                                    new OA\Property(property: "imageSecondaire3", type: "string", format: "binary"), //photo
                                    new OA\Property(property: "prix", type: "string"),
                                    new OA\Property(property: "numFace", type: "string"),
                                    new OA\Property(property: "code", type: "string"),
                                ]
                            ),
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
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
        Utils $utils,
    ): Response {

        try {
            $names = 'document_' . '01';
            $filePrefix  = str_slug($names);
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
            // dd($filePath);


            $data = json_decode($request->getContent(), true);

            $panneau = new Panneau();
            $panneau->setGpsLat($request->get('gpslat'));
            $panneau->setGpsLong($request->get('gpslong'));
            $panneau->setType($typeRepository->find($request->get('type')));
            $panneau->setIllumination($illuminationRepository->find($request->get('illumination')));
            $panneau->setSousType($sousTypeRepository->find($request->get('soustype')));
            $panneau->setSubstrat($substratRepository->find($request->get('substrat')));
            $panneau->setLocalite($localiteRepository->find($request->get('localite')));
            $panneau->setTaille($tailleRepository->find($request->get('taille')));
            $panneau->setSuperficie($superficieRepository->find($request->get('superficie')));
            $panneau->setOrientation($orientationRepository->find($request->get('orientation')));
            $panneau->setCode($request->get('code'));
            $panneau->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
            $panneau->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
            $panneau->setCreatedAtValue(new DateTime());
            $panneau->setUpdatedAt(new DateTime());


            $facesData = $request->get('lignes', []);
            $uploadedFiles = $request->files->get('lignes', []);

            foreach ($facesData as $index => $faceData) {
                $newFace = new Face();
                $newFace
                    ->setNumFace($faceData['numFace'] ?? '')
                    ->setCode($faceData['code'] ?? '');

                // 4. Upload des Fichiers (Optimisé)
                if (isset($uploadedFiles[$index])) {
                    $this->processUploadedFiles(
                        $uploadedFiles[$index],
                        $newFace,
                        $utils,
                        $filePath,
                        $filePrefix
                    );
                }

                $panneau->addFace($newFace);
            }




            $errorResponse = $this->errorResponse($panneau);
            if ($errorResponse !== null) {
                return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
            } else {

                $panneauRepository->add($panneau, true);
            }
        } catch (\Throwable $th) {
            // Log de l'erreur (ex: dans var/log/)
            file_put_contents(
                $this->getParameter('kernel.logs_dir') . '/upload_errors.log',
                '[' . date('Y-m-d H:i:s') . '] ' . $th->getMessage() . PHP_EOL,
                FILE_APPEND
            );
            return $this->json(['error' => 'Erreur lors du traitement'], 500);
        }

        return $this->responseData($panneau, 'group1', ['Content-Type' => 'application/json']);
    }


    private function processUploadedFiles(
        array $files,
        Face $face,
        Utils $utils,
        string $filePath,
        string $filePrefix
    ): void {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxFileSize = 10 * 1024 * 1024; // 10 Mo

        foreach ($files as $key => $uploadedFile) {
            if (!$uploadedFile instanceof UploadedFile) {
                continue;
            }

            // Validation du fichier
            if (
                !in_array($uploadedFile->getMimeType(), $allowedMimeTypes) ||
                $uploadedFile->getSize() > $maxFileSize
            ) {
                continue; // ou throw une exception
            }

            // Upload sécurisé
            $filename = $utils->sauvegardeFichier(
                $filePath,
                $filePrefix . '_' . uniqid(),
                $uploadedFile,
                self::UPLOAD_PATH
            );

            if ($filename) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($face, $setter)) {
                    $face->$setter($filename);
                }
            }
        }
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de panneau",
        description: "Permet de créer un panneau.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "gpslat", type: "string"),
                        new OA\Property(property: "gpslong", type: "string"),
                        new OA\Property(property: "type", type: "string"),
                        new OA\Property(property: "illumination", type: "string"),
                        new OA\Property(property: "soustype", type: "string"),
                        new OA\Property(property: "substrat", type: "string"),
                        new OA\Property(property: "localite", type: "string"),
                        new OA\Property(property: "taille", type: "string"),
                        new OA\Property(property: "superficie", type: "string"),
                        new OA\Property(property: "orientation", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),

                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
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
        UserRepository $userRepository
    ): Response {
        try {
            $data = json_decode($request->getContent());
            if ($panneau != null) {


                $panneau->setCode($request->get('code'));
                $panneau->setGpsLat($request->get('gpslat'));
                $panneau->setGpsLong($request->get('gpslong'));
                $panneau->setType($typeRepository->find($request->get('type')));
                $panneau->setIllumination($illuminationRepository->find($request->get('illumination')));
                $panneau->setSousType($sousTypeRepository->find($request->get('soustype')));
                $panneau->setSubstrat($substratRepository->find($request->get('substrat')));
                $panneau->setLocalite($localiteRepository->find($request->get('localite')));
                $panneau->setTaille($tailleRepository->find($request->get('taille')));
                $panneau->setSuperficie($superficieRepository->find($request->get('superficie')));
                $panneau->setOrientation($orientationRepository->find($request->get('orientation')));

                $panneau->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $panneau->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($panneau);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $panneauRepository->add($panneau, true);
                }



                // On retourne la confirmation
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

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) panneau.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) panneau',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Panneau::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'panneau')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Panneau $panneau, PanneauRepository $villeRepository): Response
    {
        try {

            if ($panneau != null) {

                $villeRepository->remove($panneau, true);

                // On retourne la confirmation
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

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs panneau.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Panneau::class, groups: ['full']))

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
