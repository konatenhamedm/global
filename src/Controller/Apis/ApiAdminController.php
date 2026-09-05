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
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/admin')]
class ApiAdminController extends ApiInterface
{
    /** Schéma de réponse succès réutilisable pour un admin */
    private function adminSuccessSchema(): OA\JsonContent
    {
        return new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                        new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                        new OA\Property(property: "genre", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                        new OA\Property(property: "civilite", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                        new OA\Property(property: "fonction", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        );
    }


    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister tous les admins",
        description: "Retourne la liste complète de tous les administrateurs enregistrés."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des admins récupérée avec succès",
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
                            new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                            new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                            new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
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
                new OA\Property(property: "data", type: "string", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Tag(name: 'admin')]
    public function index(AdminRepository $adminRepository): Response
    {
        try {
            $admins = $adminRepository->findAll();
            $response =  $this->responseData($admins, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un admin par ID",
        description: "Retourne les détails d'un administrateur à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de l'admin",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Admin trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                        new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                        new OA\Property(property: "genre", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                        new OA\Property(property: "civilite", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                        new OA\Property(property: "fonction", type: "object", properties: [new OA\Property(property: "id", type: "integer"), new OA\Property(property: "libelle", type: "string")]),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Admin non trouvé (identifiant inexistant)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'admin')]
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


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouvel administrateur",
        description: "Crée un nouvel administrateur avec son compte utilisateur associé (email + mot de passe).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nom', 'prenoms', 'email', 'password', 'contact', 'genre', 'civilite', 'fonction', 'userUpdate'],
                properties: [
                    new OA\Property(property: "genre", type: "integer", description: "ID du genre", example: 1),
                    new OA\Property(property: "civilite", type: "integer", description: "ID de la civilité", example: 1),
                    new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                    new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                    new OA\Property(property: "fonction", type: "integer", description: "ID de la fonction", example: 2),
                    new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "motdepasse123"),
                    new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui effectue l'opération", example: 1),
                ],
                type: "object"
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Administrateur créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 5),
                        new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                        new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
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
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le nom est obligatoire.", "L'email est invalide."]),
            ]
        )
    )]
    #[OA\Tag(name: 'admin')]
    public function create(Request $request, AdminRepository $adminRepository, GenreRepository $genreRepository, CiviliteRepository $civiliteRepository, FonctionRepository $fonctionRepository, UserRepository $userRepository): Response
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
            return $errorResponse;
        } else {
            $user = new User();
            $user->setEmail($request->get('email'));
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->hasher->hashPassword($user, $request->get('password')));

            $errorResponseUser = $this->errorResponse($admin);
            if ($errorResponseUser !== null) {
                return $errorResponseUser;
            } else {
                $adminRepository->add($admin, true);
                $user->setPersonne($admin);
                $userRepository->add($user, true);
            }
        }

        return $this->responseData($admin, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier un administrateur existant",
        description: "Met à jour les informations d'un administrateur identifié par son ID.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "genre", type: "integer", description: "ID du genre", example: 1),
                    new OA\Property(property: "civilite", type: "integer", description: "ID de la civilité", example: 1),
                    new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                    new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                    new OA\Property(property: "fonction", type: "integer", description: "ID de la fonction", example: 2),
                    new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                    new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui effectue la modification", example: 1),
                ],
                type: "object"
            )
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de l'admin à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Administrateur modifié avec succès",
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
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le nom est obligatoire."]),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Admin non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'admin')]
    public function update(Request $request, Admin $admin, AdminRepository $adminRepository, GenreRepository $genreRepository, CiviliteRepository $civiliteRepository, FonctionRepository $fonctionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($admin != null) {

                $admin->setGenre($genreRepository->find($request->get('genre')));
                $admin->setCivilite($civiliteRepository->find($request->get('civilite')));
                $admin->setNom($request->get('nom'));
                $admin->setPrenoms($request->get('prenoms'));
                $admin->setContact($request->get('contact'));
                $admin->setFonction($fonctionRepository->find($request->get('fonction')));
                $admin->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
                $admin->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $admin->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($admin);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $adminRepository->add($admin, true);
                }

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


    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer un administrateur",
        description: "Supprime définitivement un administrateur à partir de son ID."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique de l'admin à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Administrateur supprimé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Admin non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'admin')]
    public function delete(Request $request, Admin $admin, AdminRepository $villeRepository): Response
    {
        try {
            if ($admin != null) {
                $villeRepository->remove($admin, true);
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

    #[Route('/delete/all', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer plusieurs administrateurs",
        description: "Supprime une liste d'administrateurs en passant leurs IDs dans le corps de la requête.",
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
        description: "Administrateurs supprimés avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'admin')]
    public function deleteAll(Request $request, AdminRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
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
