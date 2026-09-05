<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ClientDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Client;
use App\Entity\TypeClient;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\CiviliteRepository;
use App\Repository\FonctionRepository;
use App\Repository\GenreRepository;
use App\Repository\IlluminationRepository;
use App\Repository\OrientationRepository;
use App\Repository\SousTypeRepository;
use App\Repository\SpecificationRepository;
use App\Repository\SubstratRepository;
use App\Repository\SuperficieRepository;
use App\Repository\TailleRepository;
use App\Repository\TypeClientRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/client')]
class ApiClientController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister tous les clients",
        description: "Retourne la liste complète de tous les clients (particuliers et entreprises)."
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des clients récupérée avec succès",
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
                            new OA\Property(property: "typeClient", type: "string", enum: ["individual", "entreprise"], example: "individual"),
                            new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                            new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                            new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                            new OA\Property(property: "email", type: "string", example: "client@example.com"),
                            new OA\Property(property: "denomination", type: "string", nullable: true, example: "Société ABC"),
                            new OA\Property(property: "compteContribuable", type: "string", nullable: true, example: "CC123456"),
                            new OA\Property(property: "adresse", type: "string", nullable: true, example: "Abidjan, Cocody"),
                            new OA\Property(property: "registreCommerce", type: "string", nullable: true, example: "RC/ABJ/2024/001"),
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
    #[OA\Tag(name: 'client')]
    public function index(ClientRepository $clientRepository): Response
    {
        try {
            $clients = $clientRepository->findAll();
            $response =  $this->responseData($clients, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtenir un client par ID",
        description: "Retourne les détails complets d'un client (particulier ou entreprise) à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du client",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Client trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "typeClient", type: "string", enum: ["individual", "entreprise"], example: "individual"),
                        new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                        new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                        new OA\Property(property: "email", type: "string", example: "client@example.com"),
                        new OA\Property(property: "denomination", type: "string", nullable: true, example: null),
                        new OA\Property(property: "compteContribuable", type: "string", nullable: true, example: null),
                        new OA\Property(property: "adresse", type: "string", nullable: true, example: null),
                        new OA\Property(property: "registreCommerce", type: "string", nullable: true, example: null),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 300,
        description: "Client non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'client')]
    public function getOne(?Client $client)
    {
        try {
            $response =  $this->responseData($client, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un nouveau client",
        description: "Crée un client particulier ou une entreprise. Le champ `type` détermine le profil :\n- `individual` : particulier (nom, prénoms, contact, email requis)\n- `entreprise` : société (denomination, compteContribuable, adresse, etc. requis)\n\nLe champ `userId` est l'ID d'un utilisateur déjà créé (compte) à associer au client.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['type', 'userId', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "userId", type: "integer", description: "ID du compte utilisateur à associer au client", example: 3),
                        new OA\Property(property: "type", type: "string", description: "Code du type de client", enum: ["individual", "entreprise"], example: "individual"),
                        new OA\Property(property: "nom", type: "string", description: "Nom (si particulier)", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", description: "Prénoms (si particulier)", example: "Hamédine"),
                        new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "client@example.com"),
                        new OA\Property(property: "registreCommerce", type: "string", description: "Registre de commerce (si entreprise)", example: "RC/ABJ/2024/001"),
                        new OA\Property(property: "denomination", type: "string", description: "Dénomination sociale (si entreprise)", example: "Société ABC SARL"),
                        new OA\Property(property: "compteContribuable", type: "string", description: "Numéro de compte contribuable (si entreprise)", example: "CC-123456"),
                        new OA\Property(property: "addresse", type: "string", description: "Adresse physique (si entreprise)", example: "Abidjan, Cocody, Rue des Jardins"),
                        new OA\Property(property: "telComptabilite", type: "string", description: "Téléphone comptabilité (si entreprise)", example: "+2250102030405"),
                        new OA\Property(property: "emailComptabilite", type: "string", description: "Email comptabilité (si entreprise)", example: "compta@societeabc.ci"),
                        new OA\Property(property: "nomStructureFacture", type: "string", description: "Nom sur la facture (si entreprise)", example: "Société ABC SARL"),
                        new OA\Property(property: "localisation", type: "string", description: "Localisation géographique (si entreprise)", example: "Abidjan"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui effectue l'opération", example: 1),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Client créé avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 10),
                        new OA\Property(property: "typeClient", type: "string", example: "individual"),
                        new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                    ]
                ),
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
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: ["Le nom est obligatoire.", "Le type de client est invalide."]),
            ]
        )
    )]
    #[OA\Tag(name: 'client')]
    public function create(Request $request, TypeClientRepository $typeClientRepository, ClientRepository $clientRepository, GenreRepository $genreRepository, CiviliteRepository $civiliteRepository, FonctionRepository $fonctionRepository, UserRepository $userRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();

        $type = $typeClientRepository->findOneBy(['code' => $request->get('type')])->getCode();

        if ($type == "individual") {
            $client->setTypeClient($type);
            $client->setNom($request->get('nom'));
            $client->setPrenoms($request->get('prenoms'));
            $client->setContact($request->get('contact'));
            $client->setEmail($request->get('email'));
        } else {
            $client->setTypeClient($type);
            $client->setDenomination($request->get('denomination'));
            $client->setCompteContribuable($request->get('compteContribuable'));
            $client->setAdresse($request->get('addresse'));
            $client->setTelComptabilite($request->get('telComptabilite'));
            $client->setEmailComptabilite($request->get('emailComptabilite'));
            $client->setNomStructureFacture($request->get('nomStructureFacture'));
            $client->setLocalisation($request->get('localisation'));
            $client->setRegistreCommerce($request->get('registreCommerce'));
            $client->setEmail($request->get('email'));
            $client->setContact($request->get('contact'));
        }

        $client->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $client->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $client->setCreatedAtValue(new DateTime());
        $client->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($client);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $user = $userRepository->find($request->get('userId'));
            $clientRepository->add($client, true);
            $user->setPersonne($client);
            $userRepository->add($user, true);
        }

        return $this->responseData($client, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier un client existant",
        description: "Met à jour les informations d'un client (particulier ou entreprise) identifié par son ID. Le type de client déjà enregistré détermine quels champs sont mis à jour.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "type", type: "string", enum: ["individual", "entreprise"], example: "individual"),
                        new OA\Property(property: "nom", type: "string", example: "KONATÉ"),
                        new OA\Property(property: "prenoms", type: "string", example: "Hamédine"),
                        new OA\Property(property: "contact", type: "string", example: "+2250101020304"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "client@example.com"),
                        new OA\Property(property: "registreCommerce", type: "string", nullable: true, example: null),
                        new OA\Property(property: "denomination", type: "string", nullable: true, example: null),
                        new OA\Property(property: "compteContribuable", type: "string", nullable: true, example: null),
                        new OA\Property(property: "adresse", type: "string", nullable: true, example: null),
                        new OA\Property(property: "telComptabilite", type: "string", nullable: true, example: null),
                        new OA\Property(property: "emailComptabilite", type: "string", nullable: true, example: null),
                        new OA\Property(property: "nomStructureFacture", type: "string", nullable: true, example: null),
                        new OA\Property(property: "localisation", type: "string", nullable: true, example: null),
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
        description: "Identifiant unique du client à modifier",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Client modifié avec succès",
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
        description: "Client non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'client')]
    public function update(Request $request, Client $client, ClientRepository $clientRepository, GenreRepository $genreRepository, CiviliteRepository $civiliteRepository, FonctionRepository $fonctionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($client != null) {

                $typeClient = $client->getTypeClient()->getCode();
                if ($typeClient == "individual") {
                    $client->setNom($request->get('nom'));
                    $client->setPrenoms($request->get('prenoms'));
                    $client->setContact($request->get('contact'));
                    $client->setEmail($request->get('email'));
                } else {
                    $client->setDenomination($request->get('denomination'));
                    $client->setCompteContribuable($request->get('compteContribuable'));
                    $client->setAdresse($request->get('adresse'));
                    $client->setTelComptabilite($request->get('telComptabilite'));
                    $client->setEmailComptabilite($request->get('emailComptabilite'));
                    $client->setNomStructureFacture($request->get('nomStructureFacture'));
                    $client->setLocalisation($request->get('localisation'));
                    $client->setRegistreCommerce($request->get('registreCommerce'));
                    $client->setEmail($request->get('email'));
                    $client->setContact($request->get('contact'));
                }

                $client->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
                $client->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $client->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($client);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $clientRepository->add($client, true);
                }

                $response = $this->responseData($client, 'group1', ['Content-Type' => 'application/json']);
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
        summary: "Supprimer un client",
        description: "Supprime définitivement un client à partir de son identifiant."
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant unique du client à supprimer",
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Client supprimé avec succès",
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
        description: "Client non trouvé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Cette ressource est inexistante"),
                new OA\Property(property: "status", type: "integer", example: 300),
            ]
        )
    )]
    #[OA\Tag(name: 'client')]
    public function delete(Request $request, Client $client, ClientRepository $villeRepository): Response
    {
        try {
            if ($client != null) {
                $villeRepository->remove($client, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($client);
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
        summary: "Supprimer plusieurs clients",
        description: "Supprime une liste de clients en passant leurs IDs dans le corps de la requête.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ids",
                        type: "array",
                        description: "Liste des IDs clients à supprimer",
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
        description: "Clients supprimés avec succès",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "string", example: "[]"),
                new OA\Property(property: "message", type: "string", example: "Operation effectuées avec success"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'client')]
    public function deleteAll(Request $request, ClientRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $client = $villeRepository->find($value['id']);

                if ($client != null) {
                    $villeRepository->remove($client);
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
