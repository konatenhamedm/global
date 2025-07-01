<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TaxeDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Taxe;
use App\Repository\TaxeRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/taxe')]
class ApiTaxeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des taxes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taxe::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'taxe')]
    // #[Security(name: 'Bearer')]
    public function index(TaxeRepository $taxeRepository): Response
    {
        try {

            $taxes = $taxeRepository->findAll();

          

            $response =  $this->responseData($taxes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) taxe en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) taxe en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taxe::class, groups: ['full']))
            
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'taxe')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Taxe $taxe)
    {
        try {
            if ($taxe) {
                $response = $this->response($taxe);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($taxe);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) taxe.
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
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "percent", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'taxe')]
    public function create(Request $request, TaxeRepository $taxeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $taxe = new Taxe();
        $taxe->setLibelle($request->get('libelle'));
        $taxe->setPourcent($request->get('percent'));
        $taxe->setCreatedBy($this->userRepository->find($request->get('userUpdate')));
        $taxe->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
        $taxe->setCreatedAtValue(new DateTime());
        $taxe->setUpdatedAt(new DateTime());

        $errorResponse = $this->errorResponse($taxe);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $taxeRepository->add($taxe, true);
        }

        return $this->responseData($taxe, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de taxe",
        description: "Permet de créer un taxe.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "percent", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'taxe')]
    public function update(Request $request, Taxe $taxe, TaxeRepository $taxeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($taxe != null) {

                $taxe->setLibelle($request->get('libelle'));
                $taxe->setPourcent($request->get('percent'));
                $taxe->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));
                $taxe->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($taxe);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $taxeRepository->add($taxe, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($taxe, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) taxe.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) taxe',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taxe::class, groups: ['full']))
           
        )
    )]
    #[OA\Tag(name: 'taxe')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Taxe $taxe, TaxeRepository $villeRepository): Response
    {
        try {

            if ($taxe != null) {

                $villeRepository->remove($taxe, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($taxe);
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
     * Permet de supprimer plusieurs taxe.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Taxe::class, groups: ['full']))
          
        )
    )]
    #[OA\Tag(name: 'taxe')]
    public function deleteAll(Request $request, TaxeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($request->get('ids') as $key => $value) {
                $taxe = $villeRepository->find($value['id']);

                if ($taxe != null) {
                    $villeRepository->remove($taxe);
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
