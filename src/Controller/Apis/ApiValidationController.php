<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CiviliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Civilite;
use App\Entity\Commande;
use App\Entity\Face;
use App\Entity\Panneau;
use App\Entity\Validation;
use App\Repository\AvecImpressionRepository;
use App\Repository\CiviliteRepository;
use App\Repository\CommandeRepository;
use App\Repository\FaceRepository;
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


    #[Route('/commande', methods: ['POST'])]
    /**
     * Permet de valider un(e) commande.
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
                        new OA\Property(property: "montantLocation", type: "string"),
                        new OA\Property(property: "montantTotal", type: "string"),
                        new OA\Property(property: "montantPose", type: "string"),
                        new OA\Property(property: "montantImpression", type: "string"),
                        new OA\Property(property: "commentaire", type: "string"),
                        new OA\Property(property: "fichierContrat", type: "string", format: "binary"),
                        new OA\Property(property: "commandeId", type: "string"),
                        new OA\Property(property: "userId", type: "string"),
                        new OA\Property(property: "etat", type: "string"),
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
    #[OA\Tag(name: 'commande')]
    public function validationCommande(
        Request $request,
        CommandeRepository $commandeRepository,
        ValidationRepository $validationRepository,
        SendMailService $sendMailService,
        UserRepository $userRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        // dd($filePath);

        try {

            /*  - devis_attente                  
            - proforma_attente_validation    
            - contrat_attente_creation       
            - contrat_attente_validation     
            - contrat_en_cours               
            - contrat_cloture  */
            $commande = $commandeRepository->find($request->get('commandeId'));


            switch ($request->get('etat')) {
                case 'devis_attente':
                    $commande->setEtat('proforma_attente_validation');
                    $commande->setMontant($request->get('montantTotal'));
                    $commande->setMontantImpression($request->get('montantImpression'));
                    $commande->setMontantLocation($request->get('montantLocation'));
                    $commande->setMontantPose($request->get('montantPose'));

                    break;

                case 'proforma_attente_validation':
                    $commande->setEtat('contrat_attente_creation');
                    break;
                case 'contrat_attente_creation':
                    $commande->setEtat('contrat_attente_validation');
                    $image = $request->files->get('fichierContrat');

                    if ($image) {
                        $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $image, self::UPLOAD_PATH);
                        if ($fichier) {
                            $commande->setFichierContrat($fichier);
                        }
                    }
                    break;
                case 'contrat_attente_validation':
                    $commande->setEtat('contrat_en_cours');
                    break;

                default:
                    # code...
                    break;
            }
            $commande->setUpdatedAt(new DateTime());
            $commande->setUpdatedBy($this->userRepository->find($request->get('userUpdate')));

            $commandeRepository->add($commande, true);

            $validation = new Validation();
            $validation->setCommande($commande);
            $validation->getEtape($request->get('etat'));
            $validation->setDateValidation(new DateTime());
            $validation->setCommentaire($request->get('commentaire'));

            $validationRepository->add($validation, true);
            $message = "";


            if ($request->get('etat') == "devis_attente") {
                $message = "Votre dossier vient de passer l'etape d'acceptation et est en séance d'analyse";
            } elseif ($request->get('etat') == "proforma_attente_validation") {
                $message = "Votre dossier vient de passer l'etape d'acceptation et est en séance d'analyse"; ;
            } elseif ($request->get('etat') == "contrat_attente_creation") {

                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            } elseif ($request->get('etat') == "contrat_attente_validation") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            } elseif ($request->get('etat') == "contrat_en_cours") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            } elseif ($request->get('etat') == "contrat_cloture") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            }

            $email = $userRepository->find($request->get('userId'))->getUserIdentifier();

            $info_user = [
                'user' => $email,
                'etape' => $request->get('etat'),
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



    #[Route('/avec/impression', methods: ['POST'])]
    /**
     * Permet de valider avec impression.
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
                        new OA\Property(property: "dateEnvoiVisuel", type: "date"),
                        new OA\Property(property: "commentaireEnvoiVisuel", type: "string"),
                        new OA\Property(property: "envoiVisuel", type: "string", format: "binary"),


                        new OA\Property(property: "dateImpressionBat", type: "date"),
                        new OA\Property(property: "commentaireImpressionBat", type: "string"),


                        new OA\Property(property: "dateValidationBat", type: "date"),
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
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'validation')]
    public function validationAvecImpression(Request $request,FaceRepository $faceRepository, AvecImpressionRepository $avecImpressionRepository, CommandeRepository $commandeRepository): Response
    {

        try {
            $commandeId = $request->get('commandeId');
            $etape = $request->get('etape');
            $userId = $request->get('userUpdate');
            $commande = $commandeRepository->find($commandeId);

            $avecImpression = $commande->getAvecImpression();
          
            if (!$avecImpression) {
                throw new \Exception('Commande non trouvée');
            }

            // Préparation des infos de fichier
            $filePrefix = str_slug('document_01');
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            // Champs communs
            
            $avecImpression->setUpdatedAt(new \DateTime());
            $avecImpression->setUpdatedBy($this->userRepository->find($userId));

            switch ($etape) {
                case 'etape_1':
                    $avecImpression->setDateEnvoiVisuel(new \DateTime($request->get('dateEnvoiVisuel')));
                    $avecImpression->setCommentaireEnvoiVisuel($request->get('commentaireEnvoiVisuel'));
                    $this->updateFile($request->files->get('envoiVisuel'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setEnvoiVisuel($fichier);
                    });
                    $avecImpression->setEtape("etape_2");
                    break;

                case 'etape_2':
                    $avecImpression->setDateImpressionBat(new \DateTime($request->get('dateImpressionBat')));
                    $avecImpression->setCommentaireImpressionBat($request->get('commentaireImpressionBat'));
                    $avecImpression->setEtape("etape_3");

                    break;

                case 'etape_3':
                    $avecImpression->setDateValidationBat(new \DateTime($request->get('dateValidationBat')));
                    $avecImpression->setCommentaireValidationBat($request->get('commentaireValidationBat'));
                    $avecImpression->setEtape("etape_4");

                    break;

                case 'etape_4':
                    $avecImpression->setDateImpressionVisuelle(new \DateTime($request->get('dateImpressionvisuelle')));
                    $avecImpression->setCommentaireImpressionVisuelle($request->get('commentaireImpressionVisuelle'));
                    $this->updateFile($request->files->get('imageImpressionVisuelle'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setImageImpressionVisuelle($fichier);
                    });
                    $avecImpression->setEtape("etape_5");

                    break;

                case 'etape_5':
                    $avecImpression->setDateProgrammationPose(new \DateTime($request->get('dateProgrammationPose')));
                    $avecImpression->setCommentaireProgrammationPose($request->get('commentaireProgrammationPose'));
                    $avecImpression->setDateDebutPose(new \DateTime($request->get('dateDebutPose')));
                    $avecImpression->setDateFinPose(new \DateTime($request->get('dateFinPose')));
                    $avecImpression->setDateDebutAlerte(new \DateTime($request->get('dateDebutAlerte')));
                    $avecImpression->setEtape("etape_6");

                    break;

                case 'etape_6':
                    $avecImpression->setDateRapportPose(new \DateTime($request->get('dateRapportPose')));
                    $avecImpression->setCommentairePose($request->get('commentairePose'));
                    $this->updateFile($request->files->get('rapportPoseDocument'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setRapportPoseDocument($fichier);
                    });
                    $avecImpression->setEtape("etape_7");

                    break;

                case 'etape_7':
                    $avecImpression->setDateRapportDepose(new \DateTime($request->get('dateRapportDepose')));
                    $avecImpression->setCommentaireDepose($request->get('commentaireDepose'));
                    $this->updateFile($request->files->get('rapportDepose'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setRapportDepose($fichier);
                    });
                    $avecImpression->setEtape("etape_8");

                    break;

                case 'etape_8':
                    $avecImpression->setDateFinalisation(new \DateTime($request->get('dateFinalisation')));
                    $avecImpression->setCommentaireFinalisation($request->get('commentaireFinalisation'));
                    $commande->setEtat('contrat_cloture');

                    $allLigne = $commande->getLignes();
                    foreach ($allLigne as $ligne) {
                        $face = $ligne->getFace();
                        $face->setEtat(Face::ETAT['Encours']);
                        $faceRepository->add($face,true);
                    }

                    $commandeRepository->add($commande, true);
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

    #[Route('/sans/impression', methods: ['POST'])]
    /**
     * Retourne la liste des civilites.
     * 
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
            )
        )
    )]
    #[OA\Tag(name: 'validation')]
    // #[Security(name: 'Bearer')]
    public function sansImpression(Request $request,FaceRepository $faceRepository, SansImpressionRepository $sansImpressionRepository, CommandeRepository $commandeRepository): Response
    {
        try {
            $commandeId = $request->get('commandeId');
            $etape = $request->get('etape');
            $userUpdateId = $request->get('userUpdate');
            $commande = $commandeRepository->find($commandeId);
            $sansImpression =$commande->getSansImpression();
        

            $filePrefix = str_slug('document_01');
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            if (!$sansImpression) {
                throw new \Exception('Commande non trouvée');
            }

            // Données communes
            $sansImpression->setUpdatedAt(new \DateTime());
            $sansImpression->setUpdatedBy($this->userRepository->find($userUpdateId));

            switch ($etape) {
                case 'etape_1':
                    $sansImpression->setDateEnvoiBache(new \DateTime($request->get('dateEnvoiBache')));
                    $sansImpression->setCommentaireEnvoiBache($request->get('commentaireEnvoiBache'));
                    $this->updateFile($request->files->get('visualBache'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setVisualBache($fichier);
                    });
            $sansImpression->setEtape("etape_2");

                    break;

                case 'etape_2':
                    $sansImpression->setDateProgrammationPose(new \DateTime($request->get('dateProgrammationPose')));
                    $sansImpression->setCommentaireProgrammationpose($request->get('commentaireProgrammationpose'));
                    $sansImpression->setEtape("etape_3");
                    break;

                case 'etape_3':
                    $sansImpression->setDateRapportPose(new \DateTime($request->get('dateRapportPose')));
                    $sansImpression->setCommentRapportPose($request->get('commentRapportPose'));
                    $this->updateFile($request->files->get('rapportPoseImage'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setRapportPoseImage($fichier);
                    });
                    $sansImpression->setEtape("etape_4");
                    break;

                case 'etape_4':
                    $sansImpression->setDateRapportDepose(new \DateTime($request->get('dateRapportDepose')));
                    $sansImpression->setCommentaireRapportDepose($request->get('commentaireRapportDepose'));

                    $this->updateFile($request->files->get('rapportDepose'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setRapportDepose($fichier);
                    });
                    $sansImpression->setEtape("etape_5");
                    break;

                case 'etape_5':
                    $sansImpression->setDateFinalisation(new \DateTime($request->get('dateFinalisation')));
                    $sansImpression->setCommentaireFinalisation($request->get('commentaireFinalisation'));
                    $commande->setEtat('contrat_cloture');
                    $allLigne = $commande->getLignes();
                    foreach ($allLigne as $ligne) {
                        $face = $ligne->getFace();
                        $face->setEtat(Face::ETAT['Encours']);
                        $faceRepository->add($face,true);
                    }

                    $commandeRepository->add($commande, true);
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
