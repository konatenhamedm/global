<?php

namespace App\Controller;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Info(
    title: "API Panneau Publicitaire",
    version: "1.0.0",
    description: "API de gestion des panneaux publicitaires, clients, commandes et validations."
)]
class AuthController extends ApiInterface
{

    #[Route('/api/compte/user/creation', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un compte utilisateur client",
        description: "Crée un nouveau compte utilisateur avec le rôle ROLE_USER et envoie un email de confirmation.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "client@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 6, example: "motdepasse123"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "email", type: "string", example: "client@example.com"),
                                new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string"), example: ["ROLE_USER"]),
                            ]
                        ),
                        new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides (email déjà existant, mot de passe trop court, etc.)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "code", type: "integer", example: 400),
                        new OA\Property(property: "message", type: "string", example: "Validation failed"),
                        new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Cette adresse email est déjà utilisée."]),
                    ]
                )
            ),
        ]
    )]
    #[OA\Tag(name: 'authentification')]
    public function create(Request $request,
    SendMailService $sendMailService,
    UserRepository $userRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->hasher->hashPassword($user, $data['password'])
        );
        $user->setTypeUser("CLIENT");
       
        $errorResponse = $this->errorResponse($user);

        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $userRepository->add($user, true);
            $info_user = [
                'login' => $data['email'],

            ];

            $context = compact('info_user');

            // TO DO
            $sendMailService->send(
                'tester@myonmci.ci',
                $data['email'],
                'Informations',
                'content_mail',
                $context
            );

        }

        return $this->responseData($user, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    #[OA\Post(
        summary: "Connexion utilisateur client (JWT)",
        description: "Génère un token JWT pour les utilisateurs clients. Ce token doit être inclus dans les requêtes suivantes via le header `Authorization: Bearer <token>`.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: "username", type: "string", format: "email", description: "L'adresse email de l'utilisateur", example: "client@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "motdepasse123"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie — retourne un token JWT",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Identifiants invalides",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "code", type: "integer", example: 401),
                        new OA\Property(property: "message", type: "string", example: "Invalid credentials."),
                    ]
                )
            ),
        ]
    )]
    #[OA\Tag(name: 'authentification')]
    public function loginUser(Request $request): JsonResponse
    {
        return new JsonResponse(['message' => 'Cette route est gérée par LexikJWTAuthenticationBundle'], 200);
    }

    #[Route('/api/auth/send_mail', name: 'api_auth_send_mail', methods: ['POST', "GET"])]
    public function sendMail(Request $request, SendMailService $sendMailService): JsonResponse
    {
        $info_user = [
            'login' => "konatenhamed@gmail.com",
            'password' => "eeeee"
        ];

        $context = compact('info_user');

        // TO DO
        $sendMailService->send(
            'tester@myonmci.ci',
            "konatenhamed@gmail.com",
            'Informations',
            'content_mail',
            $context
        );
       
        return new JsonResponse(['message' => 'Cette route est gérée par LexikJWTAuthenticationBundle'], 200);
    }

    #[Route('/api/auth/login_check', name: 'api_auth_login_check', methods: ['POST'])]
    #[OA\Post(
        summary: "Connexion administrateur (JWT)",
        description: "Génère un token JWT pour les administrateurs du back-office. Ce token doit être inclus dans les requêtes suivantes via le header `Authorization: Bearer <token>`.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: "username", type: "string", format: "email", description: "L'adresse email de l'administrateur", example: "admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "adminpass123"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie — retourne un token JWT",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Identifiants invalides",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "code", type: "integer", example: 401),
                        new OA\Property(property: "message", type: "string", example: "Invalid credentials."),
                    ]
                )
            ),
        ]
    )]
    #[OA\Tag(name: 'authentification')]
    public function loginAdmin(Request $request): JsonResponse
    {
        return new JsonResponse(['message' => 'Cette route est gérée par LexikJWTAuthenticationBundle'], 200);
    }
}
