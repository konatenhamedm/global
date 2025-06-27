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
    /**
     * Retourne la liste des faces.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Face::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'face')]
    // #[Security(name: 'Bearer')]
    public function index(FaceRepository $faceRepository): Response
    {
        try {

            $faces = $faceRepository->findAll();



            $response =  $this->responseData($faces, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) face en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) face en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Face::class, groups: ['full']))

        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'face')]
    //#[Security(name: 'Bearer')]
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


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) face.
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
                        new OA\Property(property: "numFace", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "panneauId", type: "string"),
                        new OA\Property(property: "prix", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                        new OA\Property(property: "imagePrincipale", type: "string", format: "binary"), //photo
                        new OA\Property(property: "imageSecondaire1", type: "string", format: "binary"), //photo
                        new OA\Property(property: "imageSecondaire2", type: "string", format: "binary"), //photo
                        new OA\Property(property: "imageSecondaire3", type: "string", format: "binary"), //photo


                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
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
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $faceRepository->add($face, true);
        }

        return $this->responseData($face, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de face",
        description: "Permet de créer un face.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "numFace", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "panneauId", type: "string"),
                        new OA\Property(property: "prix", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                        new OA\Property(property: "imagePrincipale", type: "string", format: "binary"), //photo
                        new OA\Property(property: "imageSecondaire1", type: "string", format: "binary"), //photo
                        new OA\Property(property: "imageSecondaire2", type: "string", format: "binary"), //photo
                        new OA\Property(property: "imageSecondaire3", type: "string", format: "binary"), //photo


                    ],
                    type: "object"
                )
            )

        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
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
                $face->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $face->setUpdatedAt(new \DateTime());


                $errorResponse = $this->errorResponse($face);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $faceRepository->add($face, true);
                }



                // On retourne la confirmation
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

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) face.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) face',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Face::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'face')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Face $face, FaceRepository $villeRepository): Response
    {
        try {

            if ($face != null) {

                $villeRepository->remove($face, true);

                // On retourne la confirmation
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

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs face.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Face::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'face')]
    public function deleteAll(Request $request, FaceRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
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
