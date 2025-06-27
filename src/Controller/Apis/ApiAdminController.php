<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\AdminDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Admin;
use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\CiviliteRepository;
use App\Repository\FonctionRepository;
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

#[Route('/api/admin')]
class ApiAdminController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des admins.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Admin::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'admin')]
    // #[Security(name: 'Bearer')]
    public function index(AdminRepository $adminRepository): Response
    {
        try {

            $admins = $adminRepository->findAll();

          

            $response =  $this->responseData($admins, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) admin en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) admin en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Admin::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'admin')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Admin $admin)
    {
        try {
            if ($admin) {
                $response = $this->response($admin);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($admin);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) admin.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "genre", type: "string"),
                    new OA\Property(property: "civilite", type: "string"),
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "prenoms", type: "string"),
                    new OA\Property(property: "fonction", type: "string"),
                    new OA\Property(property: "contact", type: "string"),

                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'admin')]
    public function create(Request $request, AdminRepository $adminRepository,GenreRepository $genreRepository,CiviliteRepository $civiliteRepository,FonctionRepository $fonctionRepository,UserRepository $userRepository): Response
    {

        $data = json_decode($request->getContent(), true);

  


        $admin = new Admin();
        $admin->setGenre($genreRepository->find($request->get('genre')));
        $admin->setCivilite($civiliteRepository->find($request->get('civilite')));
        $admin->setNom($request->get('nom'));
        $admin->setPrenoms($request->get('prenoms'));
        $admin->setContact($request->get('contact'));
        $admin->setFonction($fonctionRepository->find($request->get('fonction')));
        $admin->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $admin->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $admin->setCreatedAtValue(new DateTime());
        $admin->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($admin);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $user = new User();
            $user->setEmail($request->get('email'));
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->hasher->hashPassword($user, $request->get('password')));

    
            $errorResponseUser = $this->errorResponse($admin);
            
            if ($errorResponseUser !== null) {
                return $errorResponseUser; // Retourne la réponse d'erreur si des erreurs sont présentes
            }else {
                $adminRepository->add($admin, true);
                $user->setPersonne($admin);
                $userRepository->add($user, true);
            }

        }

        return $this->responseData($admin, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de admin",
        description: "Permet de créer un admin.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "genre", type: "string"),
                    new OA\Property(property: "civilite", type: "string"),
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "prenoms", type: "string"),
                    new OA\Property(property: "fonction", type: "string"),
                    new OA\Property(property: "contact", type: "string"),
                
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'admin')]
    public function update(Request $request, Admin $admin, AdminRepository $adminRepository,GenreRepository $genreRepository,CiviliteRepository $civiliteRepository,FonctionRepository $fonctionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($admin != null) {

                $admin->setGenre($genreRepository->find($data->genre));
                $admin->setCivilite($civiliteRepository->find($data->civilite));
                $admin->setNom($data->nom);
                $admin->setPrenoms($data->prenoms);
                $admin->setContact($data->contact);
                $admin->setFonction($fonctionRepository->find($data->fonction));
                $admin->setCreatedBy($this->userRepository->find($data->userUpdate));
                $admin->setUpdatedBy($this->userRepository->find($data->userUpdate));
                $admin->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($admin);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $adminRepository->add($admin, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($admin, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) admin.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) admin',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Admin::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'admin')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Admin $admin, AdminRepository $villeRepository): Response
    {
        try {

            if ($admin != null) {

                $villeRepository->remove($admin, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($admin);
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
     * Permet de supprimer plusieurs admin.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Admin::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'admin')]
    public function deleteAll(Request $request, AdminRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $admin = $villeRepository->find($value['id']);

                if ($admin != null) {
                    $villeRepository->remove($admin);
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
