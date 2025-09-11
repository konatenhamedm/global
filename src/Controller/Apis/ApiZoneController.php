<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ZoneDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/zone')]
class ApiZoneController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des zones.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Zone::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'zone')]
    // #[Security(name: 'Bearer')]
    public function index(ZoneRepository $zoneRepository): Response
    {
        try {

            $zones = $zoneRepository->findAll();



            $response =  $this->responseData($zones, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) zone en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) zone en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Zone::class, groups: ['full']))

        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'zone')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Zone $zone)
    {
        try {
            if ($zone) {
                $response = $this->response($zone);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($zone);
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
                        new OA\Property(property: "centreLat", type: "string"),
                        new OA\Property(property: "centreLng", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'zone')]
    public function create(Request $request, ZoneRepository $zoneRepository): Response
    {
        $libelle = $request->request->get('libelle');
        $code = $request->request->get('code');
        $zoom = $request->request->get('zoom');
        $centreLat = $request->request->get('centreLat');
        $centreLng = $request->request->get('centreLng');
        $userUpdate = $request->request->get('userUpdate');

        $zone = new Zone();
        $zone->setLibelle($libelle);
        $zone->setZoom($zoom);
        $zone->setCentreLat($centreLat);
        $zone->setCentreLng($centreLng);
        $zone->setCode($code);
        $zone->setCreatedBy($this->userRepository->find($userUpdate));
        $zone->setUpdatedBy($this->userRepository->find($userUpdate));
        $zone->setCreatedAtValue(new \DateTime());
        $zone->setUpdatedAt(new \DateTime());

        $errorResponse = $this->errorResponse($zone);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $zoneRepository->add($zone, true);

        return $this->responseData($zone, 'group1', ['Content-Type' => 'application/json']);
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
                        new OA\Property(property: "centreLat", type: "string"),
                        new OA\Property(property: "centreLng", type: "string"),
                        new OA\Property(property: "userUpdate", type: "string"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'zone')]
    public function update(Request $request, Zone $zone, ZoneRepository $zoneRepository): Response
    {
        try {
            $libelle = $request->request->get('libelle');
            $code = $request->request->get('code');
            $zoom = $request->request->get('zoom');
            $centreLat = $request->request->get('centreLat');
        $centreLng = $request->request->get('centreLng');
            $userUpdate = $request->request->get('userUpdate');

            if ($zone !== null) {
                $zone->setLibelle($libelle);
                $zone->setCode($code);
                $zone->setCentreLat($centreLat);
                $zone->setCentreLng($centreLng);
                $zone->setZoom($zoom);
                $zone->setUpdatedBy($this->userRepository->find($userUpdate));
                $zone->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($zone);
                if ($errorResponse !== null) {
                    return $errorResponse;
                }

                $zoneRepository->add($zone, true);
                return $this->responseData($zone, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) zone.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) zone',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Zone::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'zone')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Zone $zone, ZoneRepository $villeRepository): Response
    {
        try {

            if ($zone != null) {

                $villeRepository->remove($zone, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($zone);
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
     * Permet de supprimer plusieurs zone.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Zone::class, groups: ['full']))

        )
    )]
    #[OA\Tag(name: 'zone')]
    public function deleteAll(Request $request, ZoneRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $zone = $villeRepository->find($value['id']);

                if ($zone != null) {
                    $villeRepository->remove($zone);
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
