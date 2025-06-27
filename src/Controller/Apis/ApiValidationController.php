<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CiviliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Civilite;
use App\Entity\Commande;
use App\Entity\Panneau;
use App\Entity\Validation;
use App\Repository\AvecImpressionRepository;
use App\Repository\CiviliteRepository;
use App\Repository\CommandeRepository;
use App\Repository\IlluminationRepository;
use App\Repository\OrientationRepository;
use App\Repository\PanneauRepository;
use App\Repository\SansImpressionRepository;
use App\Repository\SousTypeRepository;
use App\Repository\SpecificationRepository;
use App\Repository\SubstratRepository;
use App\Repository\SuperficieRepository;
use App\Repository\TailleRepository;
use App\Repository\TypeClientRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use App\Service\SendMailService;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/validation')]
class ApiValidationController extends ApiInterface
{


    #[Route('/commande', methods: ['GET'])]
    /**
     * Permet de valider un(e) commande.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "commentaire", type: "string"),
                    new OA\Property(property: "commandeId", type: "string"),
                    new OA\Property(property: "userId", type: "string"),
                    new OA\Property(property: "etat", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'commande')]
    public function validationCommande(
        Request $request,
        CommandeRepository $commandeRepository,
        ValidationRepository $validationRepository,
        SendMailService $sendMailService,
        UserRepository $userRepository
    ): Response {
        $data = json_decode($request->getContent(), true);

        try {

            $commande = $commandeRepository->find($data['commandeId']);

            $commande->setEtat($data['etat']);
            $commande->setUpdatedAt(new DateTime());
            $commande->setUpdatedBy($this->userRepository->find($data['userUpdate']));

            $commandeRepository->add($commande, true);

            $validation = new Validation();
            $validation->setCommande($commande);
            $validation->getEtape($data['etat']);
            $validation->setDateValidation(new DateTime());
            $validation->setCommentaire($data['commentaire']);

            $validationRepository->add($validation, true);
            $message = "";


            if ($data['etat'] == "devis_attente") {
                $message = "Votre dossier vient de passer l'etape d'acceptation et est en séance d'analyse";
            } elseif ($data['etat'] == "proforma_attente_validation") {
                $message = "Votre dossier vient de passer d'être réjeté pour la raison suivante: " . $data['commentaire'];
            } elseif ($data['etat'] == "contrat_attente_creation") {

                $message = "Votre dossier vient de passer d'être réfusé pour la raison suivante: " . $data['commentaire'];
            } elseif ($data['etat'] == "contrat_attente_validation") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            } elseif ($data['etat'] == "contrat_en_cours") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            } elseif ($data['etat'] == "contrat_cloture") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            }

            $email = $userRepository->find($data['userId'])->getUserIdentifier();

            $info_user = [
                'user' => $email,
                'etape' => $data['etat'],
                'message' => $message
            ];

            $context = compact('info_user');

            // TO DO

            $sendMailService->send(
                'tester@myonmci.ci',
                $email,
                'Validaton de la commande',
                'content_validation',
                $context
            );

            $response =  $this->responseData($commande, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    private function updateFile($file, $filePath, $filePrefix, callable $callback): void
    {
        if ($file) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $file, self::UPLOAD_PATH);
            if ($fichier) {
                $callback($fichier);
            }
        }
    }



    #[Route('/avec/impression', methods: ['GET'])]
    /**
     * Permet de valider avec impression.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "dateEnvoiVisuel", type: "date"),
                    new OA\Property(property: "commentaireEnvoiVisuel", type: "string"),
                    new OA\Property(property: "envoiVisuel", type: "string", format: "binary"),


                    new OA\Property(property: "dateImpressionBat", type: "date"),
                    new OA\Property(property: "commentaireImpressionBat", type: "string"),


                    new OA\Property(property: "DateValidationBat", type: "date"),
                    new OA\Property(property: "commentaireValidationBat", type: "string"),

                    new OA\Property(property: "dateImpressionvisuelle", type: "date"),
                    new OA\Property(property: "commentaireImpressionVisuelle", type: "string"),
                    new OA\Property(property: "imageImpressionVisuelle", type: "string", format: "binary"),

                    new OA\Property(property: "dateProgrammationPose", type: "date"),
                    new OA\Property(property: "commentaireProgrammationPose", type: "string"),
                    new OA\Property(property: "dateDebutPose", type: "date"),
                    new OA\Property(property: "dateFinPose", type: "date"),
                    new OA\Property(property: "dateDebutAlerte", type: "date"),


                    new OA\Property(property: "dateRapportPose", type: "date"),
                    new OA\Property(property: "commentairePose", type: "string"),
                    new OA\Property(property: "rapportPoseDocument", type: "string", format: "binary"),


                    new OA\Property(property: "rapportDepose", type: "string", format: "binary"),
                    new OA\Property(property: "dateRapportDepose", type: "date"),
                    new OA\Property(property: "commentaireDepose", type: "string"),


                    new OA\Property(property: "dateFinalisation", type: "date"),
                    new OA\Property(property: "commentaireFinalisation", type: "string"),


                    new OA\Property(property: "etape", type: "string"),
                    new OA\Property(property: "commandeId", type: "string"),
                    new OA\Property(property: "userUpdate", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'commande')]
    public function validationAvecImpression(Request $request, AvecImpressionRepository $avecImpressionRepository): Response
    {

        try {
            $commandeId = $request->get('commandeId');
            $etape = $request->get('etape');
            $userId = $request->get('userUpdate');

            $avecImpression = $avecImpressionRepository->findOneBy(['commande' => $commandeId]);
            if (!$avecImpression) {
                throw new \Exception('Commande non trouvée');
            }

            // Préparation des infos de fichier
            $filePrefix = str_slug('document_01');
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            // Champs communs
            $avecImpression->setEtape($etape);
            $avecImpression->setUpdatedAt(new \DateTime());
            $avecImpression->setUpdatedBy($this->userRepository->find($userId));

            switch ($etape) {
                case 'etape_1':
                    $avecImpression->setDateEnvoiVisuel(new \DateTime($request->get('dateEnvoiVisuel')));
                    $avecImpression->setCommentaireEnvoiVisuel($request->get('commentaireEnvoiVisuel'));
                    $this->updateFile($request->files->get('envoiVisuel'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setEnvoiVisuel($fichier);
                    });
                    break;

                case 'etape_2':
                    $avecImpression->setDateImpressionBat(new \DateTime($request->get('dateImpressionBat')));
                    $avecImpression->setCommentaireImpressionBat($request->get('commentaireImpressionBat'));
                    break;

                case 'etape_3':
                    $avecImpression->setDateValidationBat(new \DateTime($request->get('DateValidationBat')));
                    $avecImpression->setCommentaireValidationBat($request->get('commentaireValidationBat'));
                    break;

                case 'etape_4':
                    $avecImpression->setDateImpressionVisuelle(new \DateTime($request->get('dateImpressionvisuelle')));
                    $avecImpression->setCommentaireImpressionVisuelle($request->get('commentaireImpressionVisuelle'));
                    $this->updateFile($request->files->get('imageImpressionVisuelle'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setImageImpressionVisuelle($fichier);
                    });
                    break;

                case 'etape_5':
                    $avecImpression->setDateProgrammationPose(new \DateTime($request->get('dateProgrammationPose')));
                    $avecImpression->setCommentaireProgrammationPose($request->get('commentaireProgrammationPose'));
                    $avecImpression->setDateDebutPose(new \DateTime($request->get('dateDebutPose')));
                    $avecImpression->setDateFinPose(new \DateTime($request->get('dateFinPose')));
                    $avecImpression->setDateDebutAlerte(new \DateTime($request->get('dateDebutAlerte')));
                    break;

                case 'etape_6':
                    $avecImpression->setDateRapportPose(new \DateTime($request->get('dateRapportPose')));
                    $avecImpression->setCommentairePose($request->get('commentairePose'));
                    $this->updateFile($request->files->get('rapportPoseDocument'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setRapportPoseDocument($fichier);
                    });
                    break;

                case 'etape_7':
                    $avecImpression->setDateRapportDepose(new \DateTime($request->get('dateRapportDepose')));
                    $avecImpression->setCommentaireDepose($request->get('commentaireDepose'));
                    $this->updateFile($request->files->get('rapportDepose'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setRapportDepose($fichier);
                    });
                    break;

                case 'etape_8':
                    $avecImpression->setDateFinalisation(new \DateTime($request->get('dateFinalisation')));
                    $avecImpression->setCommentaireFinalisation($request->get('commentaireFinalisation'));
                    break;
            }

            $avecImpressionRepository->add($avecImpression, true);

            $response = $this->responseData($avecImpression, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("Erreur : " . $exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }


    private function handleFileUpload($file, callable $callback): void
    {
        if ($file) {
            $fichier = $this->utils->sauvegardeFichier(self::UPLOAD_PATH, 'document', $file, self::UPLOAD_PATH);
            if ($fichier) {
                $callback($fichier);
            }
        }
    }

    #[Route('/sans/impression', methods: ['GET'])]
    /**
     * Retourne la liste des civilites.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des panneaux avec leurs faces.',
        content: new OA\JsonContent(
            properties: [
                //ETAPE 1
                new OA\Property(property: "dateEnvoiBache", type: "date"),
                new OA\Property(property: "visualBache", type: "string"),
                new OA\Property(property: "commentaireEnvoiBache", type: "string", format: "binary"),

                //ETAPE 2
                new OA\Property(property: "dateProgrammationPose", type: "date"),
                new OA\Property(property: "commentaireProgrammationpose", type: "string"),

                //ETAPE 3

                new OA\Property(property: "dateRapportPose", type: "date"),
                new OA\Property(property: "commentRapportPose", type: "string"),
                new OA\Property(property: "rapportPoseImage", type: "string", format: "binary"),

                //ETAPE 4

                new OA\Property(property: "dateRapportDepose", type: "date"),
                new OA\Property(property: "commentaireRapportDepose", type: "string"),
                new OA\Property(property: "rapportDepose", type: "string", format: "binary"),

                //ETAPE 5
               
                new OA\Property(property: "dateFinalisation", type: "date"),
                new OA\Property(property: "commentaireFinalisation", type: "string"),


                new OA\Property(property: "etape", type: "string"),
                new OA\Property(property: "commandeId", type: "string"),
                new OA\Property(property: "userUpdate", type: "string"),

            ],
            type: "object"
        )
    )]
    #[OA\Tag(name: 'validation')]
    // #[Security(name: 'Bearer')]
    public function sansImpression(Request $request, SansImpressionRepository $sansImpressionRepository,CommandeRepository $commandeRepository): Response
    {
        try {
            $commandeId = $request->get('commandeId');
            $commandData = $commandeRepository->find($commandeId);
            $etape = $request->get('etape');
            $userUpdateId = $request->get('userUpdate');
            $sansImpression = $sansImpressionRepository->findOneBy(['commande' => $commandeId]);

            $filePrefix = str_slug('document_01');
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            if (!$sansImpression) {
                throw new \Exception('Commande non trouvée');
            }

            // Données communes
            $sansImpression->setEtape($etape);
            $sansImpression->setUpdatedAt(new \DateTime());
            $sansImpression->setUpdatedBy($this->userRepository->find($userUpdateId));

            switch ($etape) {
                case 'etape_1':
                    $sansImpression->setDateEnvoiBache(new \DateTime($request->get('dateEnvoiBache')));
                    $sansImpression->setCommentaireEnvoiBache($request->get('commentaireEnvoiBache'));
                    $this->updateFile($request->files->get('visualBache'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setVisualBache($fichier);
                    });
                    break;

                case 'etape_2':
                    $sansImpression->setDateProgrammationPose(new \DateTime($request->get('dateProgrammationPose')));
                    $sansImpression->setCommentaireProgrammationpose($request->get('commentaireProgrammationpose'));
                    break;

                case 'etape_3':
                    $sansImpression->setDateRapportPose(new \DateTime($request->get('dateRapportPose')));
                    $sansImpression->setCommentRapportPose($request->get('commentRapportPose'));
                    $this->updateFile($request->files->get('rapportPoseImage'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setRapportPoseImage($fichier);
                    });
                    break;

                case 'etape_4':
                    $sansImpression->setDateRapportDepose(new \DateTime($request->get('dateRapportDepose')));
                    $sansImpression->setCommentaireRapportDepose($request->get('commentaireRapportDepose'));

                    $this->updateFile($request->files->get('rapportDepose'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setRapportDepose($fichier);
                    });
                    break;

                case 'etape_5':
                    $sansImpression->setDateFinalisation(new \DateTime($request->get('dateFinalisation')));
                    $sansImpression->setCommentaireFinalisation($request->get('commentaireFinalisation'));
                    /* $commandData->setEtat('contrat_cloture');
                    $commandeRepository->add($commandData, true); */
                    break;
            }

            $sansImpressionRepository->add($sansImpression, true);
            $response = $this->responseData($sansImpression, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("Erreur : " . $exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }
}
