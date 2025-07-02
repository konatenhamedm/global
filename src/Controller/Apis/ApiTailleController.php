<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TailleDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Taille;
use App\Repository\TailleRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/taille')]
class ApiTailleController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des tailles.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taille::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'taille')]
    // #[Security(name: 'Bearer')]
    public function index(TailleRepository $tailleRepository): Response
    {
        try {

            $tailles = $tailleRepository->findAll();



            $response =  $this->responseData($tailles, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) taille en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) taille en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taille::class, groups: ['full']))

        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'taille')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Taille $taille)
    {
        try {
            if ($taille) {
                $response = $this->response($taille);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($taille);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    required: ["dimension", "userUpdate"],
                    properties: [
                        new OA\Property(property: "dimension", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'taille')]
    public function create(Request $request, TailleRepository $tailleRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $taille = new Taille();
        $taille->setDimension($request->get('dimension'));
        $taille->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $taille->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $taille->setCreatedAtValue(new DateTime());
        $taille->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($taille);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $tailleRepository->add($taille, true);
        }

        return $this->responseData($taille, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    required: ["dimension", "userUpdate"],
                    properties: [
                        new OA\Property(property: "dimension", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'taille')]
    public function update(Request $request, Taille $taille, TailleRepository $tailleRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($taille != null) {

                $taille->setDimension($request->get('dimension'));
                $taille->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $taille->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($taille);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $tailleRepository->add($taille, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($taille, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) taille.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) taille',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taille::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'taille')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Taille $taille, TailleRepository $villeRepository): Response
    {
        try {

            if ($taille != null) {

                $villeRepository->remove($taille, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($taille);
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
     * Permet de supprimer plusieurs taille.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taille::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'taille')]
    public function deleteAll(Request $request, TailleRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $taille = $villeRepository->find($value['id']);

                if ($taille != null) {
                    $villeRepository->remove($taille);
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
