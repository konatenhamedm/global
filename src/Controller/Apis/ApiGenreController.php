<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\GenreDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Genre;
use App\Repository\GenreRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/genre')]
class ApiGenreController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des genres.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Genre::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'genre')]
    // #[Security(name: 'Bearer')]
    public function index(GenreRepository $genreRepository): Response
    {
        try {

            $genres = $genreRepository->findAll();

          

            $response =  $this->responseData($genres, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) genre en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) genre en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Genre::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'genre')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Genre $genre)
    {
        try {
            if ($genre) {
                $response = $this->response($genre);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($genre);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) genre.
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
    #[OA\Tag(name: 'genre')]
    public function create(Request $request, GenreRepository $genreRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $genre = new Genre();
        $genre->setLibelle($request->get('libelle'));
        $genre->setCode($request->get('code'));
        $genre->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $genre->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $genre->setCreatedAtValue(new DateTime());
        $genre->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($genre);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $genreRepository->add($genre, true);
        }

        return $this->responseData($genre, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de genre",
        description: "Permet de créer un genre.",
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
    #[OA\Tag(name: 'genre')]
    public function update(Request $request, Genre $genre, GenreRepository $genreRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($genre != null) {

                $genre->setLibelle($data->libelle);
                $genre->setCode($data->code);
                $genre->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $genre->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($genre);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $genreRepository->add($genre, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($genre, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) genre.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) genre',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Genre::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'genre')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Genre $genre, GenreRepository $villeRepository): Response
    {
        try {

            if ($genre != null) {

                $villeRepository->remove($genre, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($genre);
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
     * Permet de supprimer plusieurs genre.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Genre::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'genre')]
    public function deleteAll(Request $request, GenreRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $genre = $villeRepository->find($value['id']);

                if ($genre != null) {
                    $villeRepository->remove($genre);
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
