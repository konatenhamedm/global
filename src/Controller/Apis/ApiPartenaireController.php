<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Fichier;
use App\Entity\Partenaire;
use App\Repository\PartenaireRepository;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/partenaire')]
class ApiPartenaireController extends ApiInterface
{
    private const UPLOAD_FOLDER = 'partenaires';
    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
    private const MAX_FILE_SIZE = 2097152; // 2 Mo

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister les partenaires",
        description: "Retourne la liste des partenaires ordonnés par le champ ordre croissant puis nom croissant. Endpoint public consommé par la vitrine et le panel admin."
    )]
    #[OA\Parameter(
        name: 'actif',
        in: 'query',
        required: false,
        description: "Filtre optionnel : 1 (actifs uniquement) ou 0 (inactifs uniquement). Par défaut, tous les partenaires sont retournés.",
        schema: new OA\Schema(type: 'string', enum: ['1', '0'], example: '1')
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des partenaires récupérée avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 3),
                            new OA\Property(property: "nom", type: "string", example: "MTN Côte d'Ivoire"),
                            new OA\Property(property: "siteWeb", type: "string", nullable: true, example: "https://mtn.ci"),
                            new OA\Property(property: "ordre", type: "integer", example: 1),
                            new OA\Property(property: "actif", type: "boolean", example: true),
                            new OA\Property(
                                property: "logo",
                                type: "object",
                                nullable: true,
                                properties: [
                                    new OA\Property(property: "alt", type: "string", example: "mtn_ci_6631.png"),
                                    new OA\Property(property: "path", type: "string", example: "partenaires"),
                                    new OA\Property(property: "url", type: "string", example: "http://global.ticleaders.net/uploads/partenaires/mtn_ci_6631.png"),
                                ]
                            ),
                            new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                            new OA\Property(property: "dateMaj", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                        ]
                    )
                ),
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
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
                new OA\Property(property: "error", type: "string", example: "Détail de l'erreur"),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Tag(name: 'partenaire')]
    public function index(Request $request, PartenaireRepository $partenaireRepository): Response
    {
        try {
            $actifParam = null;
            if ($request->query->has('actif')) {
                $raw = strtolower(trim((string)$request->query->get('actif')));
                if (in_array($raw, ['1', 'true'], true)) {
                    $actifParam = true;
                } elseif (in_array($raw, ['0', 'false'], true)) {
                    $actifParam = false;
                }
            }

            $partenaires = $partenaireRepository->findFiltered($actifParam);

            return $this->formatPartnerResponse($partenaires);
        } catch (\Exception $e) {
            return $this->respondServerError($e->getMessage());
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un partenaire par ID",
        description: "Retourne les détails complets d'un partenaire à partir de son identifiant technique. Endpoint public."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du partenaire",
        schema: new OA\Schema(type: 'integer', example: 3)
    )]
    #[OA\Response(
        response: 200,
        description: "Partenaire trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 3),
                        new OA\Property(property: "nom", type: "string", example: "MTN Côte d'Ivoire"),
                        new OA\Property(property: "siteWeb", type: "string", nullable: true, example: "https://mtn.ci"),
                        new OA\Property(property: "ordre", type: "integer", example: 1),
                        new OA\Property(property: "actif", type: "boolean", example: true),
                        new OA\Property(
                            property: "logo",
                            type: "object",
                            nullable: true,
                            properties: [
                                new OA\Property(property: "alt", type: "string", example: "mtn_ci_6631.png"),
                                new OA\Property(property: "path", type: "string", example: "partenaires"),
                                new OA\Property(property: "url", type: "string", example: "http://global.ticleaders.net/uploads/partenaires/mtn_ci_6631.png"),
                            ]
                        ),
                        new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                        new OA\Property(property: "dateMaj", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                    ]
                ),
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Partenaire introuvable",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Partenaire introuvable"),
                new OA\Property(property: "code", type: "integer", example: 404),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Tag(name: 'partenaire')]
    public function getOne(?int $id, PartenaireRepository $partenaireRepository): Response
    {
        $partenaire = $id ? $partenaireRepository->find($id) : null;
        if (!$partenaire) {
            return new JsonResponse([
                'message' => 'Partenaire introuvable',
                'code' => 404,
                'data' => null,
            ], 404);
        }

        return $this->formatPartnerResponse($partenaire);
    }

    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouveau partenaire",
        description: "Crée un partenaire avec upload obligatoire du logo. Corps en multipart/form-data.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['nom', 'logo', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "nom", type: "string", description: "Raison sociale / nom affiché", example: "MTN Côte d'Ivoire"),
                        new OA\Property(property: "logo", type: "string", format: "binary", description: "Fichier logo (png, jpg, jpeg, webp, svg - max 2 Mo)"),
                        new OA\Property(property: "siteWeb", type: "string", nullable: true, description: "URL absolue du site web partenaire (ex: https://mtn.ci)", example: "https://mtn.ci"),
                        new OA\Property(property: "ordre", type: "integer", description: "Ordre d'affichage croissant (défaut 0)", example: 1),
                        new OA\Property(property: "actif", type: "string", description: "Statut actif (1 ou true / 0 ou false, défaut 1)", example: "1"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'administrateur créateur", example: 4),
                    ]
                )
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Partenaire créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 3),
                        new OA\Property(property: "nom", type: "string", example: "MTN Côte d'Ivoire"),
                        new OA\Property(property: "siteWeb", type: "string", nullable: true, example: "https://mtn.ci"),
                        new OA\Property(property: "ordre", type: "integer", example: 1),
                        new OA\Property(property: "actif", type: "boolean", example: true),
                        new OA\Property(
                            property: "logo",
                            type: "object",
                            properties: [
                                new OA\Property(property: "alt", type: "string", example: "mtn_ci_6631.png"),
                                new OA\Property(property: "path", type: "string", example: "partenaires"),
                                new OA\Property(property: "url", type: "string", example: "http://global.ticleaders.net/uploads/partenaires/mtn_ci_6631.png"),
                            ]
                        ),
                        new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                        new OA\Property(property: "dateMaj", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                    ]
                ),
                new OA\Property(property: "code", type: "integer", example: 201),
                new OA\Property(property: "message", type: "string", example: "Partenaire créé avec succès"),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Erreur de validation des champs",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Données invalides"),
                new OA\Property(
                    property: "errors",
                    type: "object",
                    example: [
                        "nom" => "Le nom du partenaire est obligatoire.",
                        "logo" => "Le logo est obligatoire à la création.",
                        "siteWeb" => "L'adresse du site web doit être une URL absolue valide (ex. https://exemple.com)."
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Non authentifié (JWT Bearer manquant ou invalide)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 401),
                new OA\Property(property: "message", type: "string", example: "JWT Token not found"),
            ]
        )
    )]
    #[OA\Tag(name: 'partenaire')]
    public function create(Request $request, PartenaireRepository $partenaireRepository): Response
    {
        $authError = $this->checkAuthentication($request);
        if ($authError !== null) {
            return $authError;
        }

        $errors = [];

        // Validation du nom
        $nom = trim((string)$request->request->get('nom', ''));
        if ($nom === '') {
            $errors['nom'] = 'Le nom du partenaire est obligatoire.';
        }

        // Validation du logo (obligatoire à la création)
        $logoFile = $request->files->get('logo');
        if (!$logoFile instanceof UploadedFile) {
            $errors['logo'] = 'Le logo est obligatoire à la création.';
        } else {
            $logoValidation = $this->validateUploadedImage($logoFile);
            if ($logoValidation !== null) {
                $errors['logo'] = $logoValidation;
            }
        }

        // Validation du siteWeb (optionnel, mais si présent doit être une URL absolue)
        $siteWeb = trim((string)$request->request->get('siteWeb', ''));
        if ($siteWeb !== '') {
            if (!$this->isValidAbsoluteUrl($siteWeb)) {
                $errors['siteWeb'] = "L'adresse du site web doit être une URL absolue valide (ex. https://exemple.com).";
            }
        } else {
            $siteWeb = null;
        }

        // Validation de userUpdate
        $userUpdateId = $request->request->get('userUpdate');
        if (!$userUpdateId) {
            $errors['userUpdate'] = "L'identifiant de l'administrateur (userUpdate) est obligatoire.";
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'message' => 'Données invalides',
                'errors' => $errors,
            ], 422);
        }

        try {
            // Upload du logo
            $fichier = $this->storeUploadedLogo($logoFile);

            $partenaire = new Partenaire();
            $partenaire->setNom($nom);
            $partenaire->setLogo($fichier);
            $partenaire->setSiteWeb($siteWeb);

            $ordre = $request->request->get('ordre', 0);
            $partenaire->setOrdre((int)$ordre);

            $actif = $request->request->get('actif', true);
            $partenaire->setActif(!in_array(strtolower((string)$actif), ['0', 'false'], true));

            if ($userUpdateId) {
                $partenaire->setUserUpdate($this->userRepository->find($userUpdateId));
            }

            $partenaire->setDateCreation(new \DateTime());
            $partenaire->setDateMaj(new \DateTime());

            $partenaireRepository->add($partenaire, true);

            $this->setMessage('Partenaire créé avec succès');
            return $this->formatPartnerResponse($partenaire, 201);
        } catch (\Exception $e) {
            return $this->respondServerError($e->getMessage());
        }
    }

    #[Route('/update/{id}', methods: ['POST'])]
    #[OA\Post(
        summary: "Modifier un partenaire existant",
        description: "Mise à jour partielle (PATCH) d'un partenaire. Tous les champs sont optionnels sauf userUpdate. Si aucun nouveau logo n'est envoyé, le logo existant est conservé. Si un logo est envoyé, l'ancien fichier est supprimé du disque.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['userUpdate'],
                    properties: [
                        new OA\Property(property: "nom", type: "string", description: "Nouveau nom", example: "MTN CI"),
                        new OA\Property(property: "logo", type: "string", format: "binary", description: "Nouveau logo (remplace l'ancien ; laisser vide pour conserver)", nullable: true),
                        new OA\Property(property: "siteWeb", type: "string", nullable: true, description: "Nouveau site web (ou vide pour supprimer le lien)", example: "https://mtn.ci"),
                        new OA\Property(property: "ordre", type: "integer", description: "Nouvel ordre d'affichage", example: 2),
                        new OA\Property(property: "actif", type: "string", description: "Statut actif (1/0 ou true/false)", example: "1"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'administrateur qui modifie", example: 4),
                    ]
                )
            )
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du partenaire à modifier",
        schema: new OA\Schema(type: 'integer', example: 3)
    )]
    #[OA\Response(
        response: 200,
        description: "Partenaire mis à jour avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 3),
                        new OA\Property(property: "nom", type: "string", example: "MTN CI"),
                        new OA\Property(property: "siteWeb", type: "string", nullable: true, example: "https://mtn.ci"),
                        new OA\Property(property: "ordre", type: "integer", example: 2),
                        new OA\Property(property: "actif", type: "boolean", example: true),
                        new OA\Property(
                            property: "logo",
                            type: "object",
                            properties: [
                                new OA\Property(property: "alt", type: "string", example: "mtn_ci_6631.png"),
                                new OA\Property(property: "path", type: "string", example: "partenaires"),
                                new OA\Property(property: "url", type: "string", example: "http://global.ticleaders.net/uploads/partenaires/mtn_ci_6631.png"),
                            ]
                        ),
                        new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                        new OA\Property(property: "dateMaj", type: "string", format: "date-time", example: "2026-09-01T10:12:00+00:00"),
                    ]
                ),
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Partenaire mis à jour avec succès"),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Partenaire introuvable",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Partenaire introuvable"),
                new OA\Property(property: "code", type: "integer", example: 404),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Erreur de validation",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Données invalides"),
                new OA\Property(property: "errors", type: "object", example: ["siteWeb" => "L'adresse du site web doit être une URL absolue valide."]),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Non authentifié (JWT Bearer manquant ou invalide)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 401),
                new OA\Property(property: "message", type: "string", example: "JWT Token not found"),
            ]
        )
    )]
    #[OA\Tag(name: 'partenaire')]
    public function update(int $id, Request $request, PartenaireRepository $partenaireRepository): Response
    {
        $authError = $this->checkAuthentication($request);
        if ($authError !== null) {
            return $authError;
        }

        $partenaire = $partenaireRepository->find($id);
        if (!$partenaire) {
            return new JsonResponse([
                'message' => 'Partenaire introuvable',
                'code' => 404,
                'data' => null,
            ], 404);
        }

        $errors = [];

        // Validation userUpdate
        $userUpdateId = $request->request->get('userUpdate');
        if (!$userUpdateId) {
            $errors['userUpdate'] = "L'identifiant de l'administrateur (userUpdate) est obligatoire.";
        }

        // Validation nom si fourni
        if ($request->request->has('nom')) {
            $nom = trim((string)$request->request->get('nom'));
            if ($nom === '') {
                $errors['nom'] = 'Le nom du partenaire ne peut pas être vide.';
            } else {
                $partenaire->setNom($nom);
            }
        }

        // Validation siteWeb si fourni
        if ($request->request->has('siteWeb')) {
            $siteWeb = trim((string)$request->request->get('siteWeb'));
            if ($siteWeb === '') {
                $partenaire->setSiteWeb(null);
            } else {
                if (!$this->isValidAbsoluteUrl($siteWeb)) {
                    $errors['siteWeb'] = "L'adresse du site web doit être une URL absolue valide (ex. https://exemple.com).";
                } else {
                    $partenaire->setSiteWeb($siteWeb);
                }
            }
        }

        // Ordre si fourni
        if ($request->request->has('ordre')) {
            $partenaire->setOrdre((int)$request->request->get('ordre'));
        }

        // Actif si fourni
        if ($request->request->has('actif')) {
            $actif = $request->request->get('actif');
            $partenaire->setActif(!in_array(strtolower((string)$actif), ['0', 'false'], true));
        }

        // Gestion du logo si un nouveau fichier est transmis
        $logoFile = $request->files->get('logo');
        if ($logoFile instanceof UploadedFile) {
            $logoValidation = $this->validateUploadedImage($logoFile);
            if ($logoValidation !== null) {
                $errors['logo'] = $logoValidation;
            }
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'message' => 'Données invalides',
                'errors' => $errors,
            ], 422);
        }

        try {
            if ($logoFile instanceof UploadedFile) {
                // Supprimer l'ancien fichier physique si existant
                $this->removePhysicalLogo($partenaire->getLogoFichier());

                // Stocker le nouveau fichier
                $nouveauFichier = $this->storeUploadedLogo($logoFile);
                $partenaire->setLogo($nouveauFichier);
            }

            if ($userUpdateId) {
                $partenaire->setUserUpdate($this->userRepository->find($userUpdateId));
            }

            $partenaire->setDateMaj(new \DateTime());
            $partenaireRepository->add($partenaire, true);

            $this->setMessage('Partenaire mis à jour avec succès');
            return $this->formatPartnerResponse($partenaire, 200);
        } catch (\Exception $e) {
            return $this->respondServerError($e->getMessage());
        }
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer définitivement un partenaire",
        description: "Supprime le partenaire en base de données ainsi que son fichier logo physique sur le serveur."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du partenaire à supprimer",
        schema: new OA\Schema(type: 'integer', example: 3)
    )]
    #[OA\Response(
        response: 200,
        description: "Partenaire supprimé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 3),
                        new OA\Property(property: "deleted", type: "boolean", example: true),
                    ]
                ),
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Partenaire supprimé avec succès"),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Partenaire introuvable",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Partenaire introuvable"),
                new OA\Property(property: "code", type: "integer", example: 404),
                new OA\Property(property: "data", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Non authentifié (JWT Bearer manquant ou invalide)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 401),
                new OA\Property(property: "message", type: "string", example: "JWT Token not found"),
            ]
        )
    )]
    #[OA\Tag(name: 'partenaire')]
    public function delete(int $id, Request $request, PartenaireRepository $partenaireRepository): Response
    {
        $authError = $this->checkAuthentication($request);
        if ($authError !== null) {
            return $authError;
        }

        $partenaire = $partenaireRepository->find($id);
        if (!$partenaire) {
            return new JsonResponse([
                'message' => 'Partenaire introuvable',
                'code' => 404,
                'data' => null,
            ], 404);
        }

        try {
            // Suppression physique du fichier logo
            $this->removePhysicalLogo($partenaire->getLogoFichier());

            // Suppression en base
            $partenaireRepository->remove($partenaire, true);

            return new JsonResponse([
                'data' => [
                    'id' => $id,
                    'deleted' => true,
                ],
                'code' => 200,
                'message' => 'Partenaire supprimé avec succès',
            ], 200);
        } catch (\Exception $e) {
            return $this->respondServerError($e->getMessage());
        }
    }

    #[Route('/reorder', methods: ['POST'])]
    #[OA\Post(
        summary: "Réordonnancement en lot des partenaires",
        description: "Permet de mettre à jour les ordres d'affichage en une seule requête JSON (drag & drop admin).",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ordre",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 3),
                                new OA\Property(property: "ordre", type: "integer", example: 1),
                            ]
                        )
                    ),
                    new OA\Property(property: "userUpdate", type: "integer", example: 4),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Ordre mis à jour avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "updated", type: "integer", example: 2),
                    ]
                ),
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Réordonnancement effectué avec succès"),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Requête invalide",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 400),
                new OA\Property(property: "message", type: "string", example: "Le corps de la requête doit contenir un tableau 'ordre'."),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Non authentifié",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 401),
                new OA\Property(property: "message", type: "string", example: "JWT Token not found"),
            ]
        )
    )]
    #[OA\Tag(name: 'partenaire')]
    public function reorder(Request $request, PartenaireRepository $partenaireRepository): Response
    {
        $authError = $this->checkAuthentication($request);
        if ($authError !== null) {
            return $authError;
        }

        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || !isset($content['ordre']) || !is_array($content['ordre'])) {
            return new JsonResponse([
                'message' => "Le corps de la requête doit contenir un tableau 'ordre'.",
                'code' => 400,
            ], 400);
        }

        $user = null;
        if (isset($content['userUpdate'])) {
            $user = $this->userRepository->find($content['userUpdate']);
        }

        $count = 0;
        foreach ($content['ordre'] as $item) {
            if (isset($item['id'], $item['ordre'])) {
                $partenaire = $partenaireRepository->find($item['id']);
                if ($partenaire) {
                    $partenaire->setOrdre((int)$item['ordre']);
                    $partenaire->setDateMaj(new \DateTime());
                    if ($user) {
                        $partenaire->setUserUpdate($user);
                    }
                    $count++;
                }
            }
        }

        $this->em->flush();

        return new JsonResponse([
            'data' => [
                'updated' => $count,
            ],
            'code' => 200,
            'message' => 'Réordonnancement effectué avec succès',
        ], 200);
    }

    private function formatPartnerResponse(mixed $data, int $status = 200): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', [
            AbstractNormalizer::GROUPS => ['group1', 'partenaire'],
        ]);

        return new JsonResponse([
            'data' => json_decode($json),
            'code' => $status,
            'message' => $this->getMessage(),
        ], $status);
    }

    private function validateUploadedImage(UploadedFile $file): ?string
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Support SVG spécifique
        $isSvg = in_array($extension, ['svg'], true) || in_array($mimeType, ['image/svg+xml', 'image/svg'], true);

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true) && !$isSvg) {
            return 'Format de logo non supporté. Formats acceptés : ' . implode(', ', self::ALLOWED_EXTENSIONS) . '.';
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return 'Le fichier logo ne doit pas dépasser 2 Mo.';
        }

        return null;
    }

    private function storeUploadedLogo(UploadedFile $file): Fichier
    {
        $uploadDir = $this->getParameter('upload_dir') . '/' . self::UPLOAD_FOLDER;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        if (!$extension || $extension === 'txt') {
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = strtolower(preg_replace('/[^a-z0-9_-]/i', '_', $originalName));
        $cleanName = trim($cleanName, '_') ?: 'partenaire';

        $fileName = substr($cleanName, 0, 80) . '_' . uniqid() . '.' . $extension;
        $file->move($uploadDir, $fileName);

        $savedPath = $uploadDir . '/' . $fileName;
        $fileSize = file_exists($savedPath) ? filesize($savedPath) : 0;

        $fichier = new Fichier();
        $fichier->setAlt($fileName);
        $fichier->setPath(self::UPLOAD_FOLDER);
        $fichier->setSize($fileSize);
        $fichier->setUrl($extension);

        return $fichier;
    }

    private function removePhysicalLogo(?Fichier $fichier): void
    {
        if (!$fichier) {
            return;
        }

        $alt = $fichier->getAlt();
        $path = $fichier->getPath() ?: self::UPLOAD_FOLDER;
        if ($alt) {
            $filePath = $this->getParameter('upload_dir') . '/' . trim($path, '/') . '/' . $alt;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }

    private function isValidAbsoluteUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }

    private function checkAuthentication(Request $request): ?JsonResponse
    {
        // Si l'utilisateur est authentifié par JWT ou session
        if ($this->getUser() !== null) {
            return null;
        }

        // Si l'en-tête Authorization est absent ou non Bearer
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse([
                'code' => 401,
                'message' => 'JWT Token not found',
            ], 401);
        }

        return null;
    }
}
