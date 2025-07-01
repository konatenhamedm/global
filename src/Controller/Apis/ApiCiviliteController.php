<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CiviliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Civilite;
use App\Repository\CiviliteRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/civilite')]
class ApiCiviliteController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des civilites.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'civilite')]
    // #[Security(name: 'Bearer')]
    public function index(CiviliteRepository $civiliteRepository): Response
    {
        try {

            $civilites = $civiliteRepository->findAll();



            $response =  $this->responseData($civilites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) civilite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) civilite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))

        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'civilite')]
    //#[Security(name: 'Bearer')]
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
        summary: "Création de civilité",
        description: "Permet de créer une civilité.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    required: ["libelle", "code", "userUpdate"],
                    properties: [
                        new OA\Property(property: "libelle", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'civilite')]
    public function create(Request $request, CiviliteRepository $civiliteRepository): Response
    {
        $libelle = $request->request->get('libelle');
        $code = $request->request->get('code');
        $userUpdate = $request->request->get('userUpdate');

        $civilite = new Civilite();
        $civilite->setLibelle($libelle);
        $civilite->setCode($code);
        $civilite->setCreatedBy($this->userRepository->find($userUpdate));
        $civilite->setUpdatedBy($this->userRepository->find($userUpdate));
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
        summary: "Mise à jour de civilité",
        description: "Permet de mettre à jour une civilité.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    required: ["libelle", "code", "userUpdate"],
                    properties: [
                        new OA\Property(property: "libelle", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'civilite')]
    public function update(Request $request, Civilite $civilite, CiviliteRepository $civiliteRepository): Response
    {
        try {
            $libelle = $request->request->get('libelle');
            $code = $request->request->get('code');
            $userUpdate = $request->request->get('userUpdate');

            if ($civilite !== null) {
                $civilite->setLibelle($libelle);
                $civilite->setCode($code);
                $civilite->setUpdatedBy($this->userRepository->find($userUpdate));
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


    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) civilite.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) civilite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'civilite')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Civilite $civilite, CiviliteRepository $villeRepository): Response
    {
        try {

            if ($civilite != null) {

                $villeRepository->remove($civilite, true);

                // On retourne la confirmation
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

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs civilite.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'civilite')]
    public function deleteAll(Request $request, CiviliteRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $civilite = $villeRepository->find($value['id']);

                if ($civilite != null) {
                    $villeRepository->remove($civilite);
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
