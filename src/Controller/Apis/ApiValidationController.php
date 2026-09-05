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
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/validation')]
class ApiValidationController extends ApiInterface
{
    #[Route('/commande', methods: ['POST'])]
    #[OA\Post(
        summary: "Faire avancer l'état d'une commande (workflow de validation)",
        description: "Fait progresser une commande d'un état à l'autre. L'état actuel (`etat`) détermine la transition :\n\n| État actuel (`etat` envoyé) | Transition vers | Action |  \n|---|---|---|\n| `devis_attente` | `proforma_attente_validation` | Enregistre les montants |\n| `proforma_attente_validation` | `contrat_attente_creation` | Validation client |\n| `contrat_attente_creation` | `contrat_attente_validation` | Upload du fichier contrat |\n| `contrat_attente_validation` | `contrat_en_cours` | Signature finale |\n\nUn email de notification est envoyé au client à chaque étape.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['commandeId', 'etat', 'userId', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "commandeId", type: "integer", description: "ID de la commande à faire avancer", example: 5),
                        new OA\Property(
                            property: "etat",
                            type: "string",
                            description: "État ACTUEL de la commande (détermine la transition)",
                            enum: ["devis_attente", "proforma_attente_validation", "contrat_attente_creation", "contrat_attente_validation"],
                            example: "devis_attente"
                        ),
                        new OA\Property(property: "montantTotal", type: "number", description: "Montant total (requis si etat=devis_attente)", example: 3200000),
                        new OA\Property(property: "montantLocation", type: "number", description: "Montant location (requis si etat=devis_attente)", example: 3000000),
                        new OA\Property(property: "montantImpression", type: "number", description: "Montant impression (requis si etat=devis_attente)", example: 200000),
                        new OA\Property(property: "montantPose", type: "number", description: "Montant pose (requis si etat=devis_attente)", example: 0),
                        new OA\Property(property: "commentaire", type: "string", description: "Commentaire sur la validation", example: "Dossier conforme, montants validés."),
                        new OA\Property(property: "fichierContrat", type: "string", format: "binary", description: "Fichier contrat PDF (requis si etat=contrat_attente_creation)"),
                        new OA\Property(property: "userId", type: "integer", description: "ID de l'utilisateur client (pour l'envoi d'email)", example: 3),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur admin qui effectue la validation", example: 1),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Commande avancée avec succès vers l'état suivant",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    description: "Commande mise à jour",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 5),
                        new OA\Property(property: "etat", type: "string", example: "proforma_attente_validation"),
                        new OA\Property(property: "montant", type: "number", example: 3200000),
                        new OA\Property(property: "montantLocation", type: "number", example: 3000000),
                        new OA\Property(property: "montantImpression", type: "number", example: 200000),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur serveur (commande introuvable, erreur email, etc.)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: ""),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'validation')]
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

        try {
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
                $message = "Votre dossier vient de passer l'etape d'acceptation et est en séance d'analyse";
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
    #[OA\Post(
        summary: "Avancer le workflow d'une commande AVEC impression (8 étapes)",
        description: "Permet de faire progresser le traitement d'une commande avec impression visuelle. Chaque appel avance l'étape d'une unité. Envoyer l'`etape` actuelle pour qu'elle passe à la suivante.\n\n| Étape envoyée | Champs requis | Passe à |\n|---|---|---|\n| `etape_1` | dateEnvoiVisuel, commentaireEnvoiVisuel, envoiVisuel (fichier) | etape_2 |\n| `etape_2` | dateImpressionBat, commentaireImpressionBat | etape_3 |\n| `etape_3` | dateValidationBat, commentaireValidationBat | etape_4 |\n| `etape_4` | dateImpressionvisuelle, commentaireImpressionVisuelle, imageImpressionVisuelle (fichier) | etape_5 |\n| `etape_5` | dateProgrammationPose, dateDebutPose, dateFinPose, dateDebutAlerte, commentaireProgrammationPose | etape_6 |\n| `etape_6` | dateRapportPose, commentairePose, rapportPose (fichier) | etape_7 |\n| `etape_7` | dateRapportDepose, commentaireRapportDepose, rapportDepose (fichier) | etape_8 |\n| `etape_8` | dateFinalisation, commentaireFinalisation | → commande clôturée |",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['commandeId', 'etape', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "commandeId", type: "integer", description: "ID de la commande", example: 5),
                        new OA\Property(property: "etape", type: "string", description: "Étape actuelle à valider", enum: ["etape_1", "etape_2", "etape_3", "etape_4", "etape_5", "etape_6", "etape_7", "etape_8"], example: "etape_1"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui effectue l'action", example: 1),

                        new OA\Property(property: "dateEnvoiVisuel", type: "string", format: "date", description: "[etape_1] Date d'envoi du visuel", example: "2025-01-20"),
                        new OA\Property(property: "commentaireEnvoiVisuel", type: "string", description: "[etape_1] Commentaire", example: "Visuel envoyé pour validation"),
                        new OA\Property(property: "envoiVisuel", type: "string", format: "binary", description: "[etape_1] Fichier du visuel envoyé"),

                        new OA\Property(property: "dateImpressionBat", type: "string", format: "date", description: "[etape_2] Date impression BAT"),
                        new OA\Property(property: "commentaireImpressionBat", type: "string", description: "[etape_2] Commentaire impression BAT"),

                        new OA\Property(property: "dateValidationBat", type: "string", format: "date", description: "[etape_3] Date validation BAT"),
                        new OA\Property(property: "commentaireValidationBat", type: "string", description: "[etape_3] Commentaire validation BAT"),

                        new OA\Property(property: "dateImpressionvisuelle", type: "string", format: "date", description: "[etape_4] Date impression visuelle"),
                        new OA\Property(property: "commentaireImpressionVisuelle", type: "string", description: "[etape_4] Commentaire"),
                        new OA\Property(property: "imageImpressionVisuelle", type: "string", format: "binary", description: "[etape_4] Image de l'impression visuelle"),

                        new OA\Property(property: "dateProgrammationPose", type: "string", format: "date", description: "[etape_5] Date de programmation de la pose"),
                        new OA\Property(property: "commentaireProgrammationPose", type: "string", description: "[etape_5] Commentaire pose"),
                        new OA\Property(property: "dateDebutPose", type: "string", format: "date", description: "[etape_5] Date début de pose"),
                        new OA\Property(property: "dateFinPose", type: "string", format: "date", description: "[etape_5] Date fin de pose"),
                        new OA\Property(property: "dateDebutAlerte", type: "string", format: "date", description: "[etape_5] Date de début d'alerte"),

                        new OA\Property(property: "dateRapportPose", type: "string", format: "date", description: "[etape_6] Date du rapport de pose"),
                        new OA\Property(property: "commentairePose", type: "string", description: "[etape_6] Commentaire pose"),
                        new OA\Property(property: "rapportPose", type: "string", format: "binary", description: "[etape_6] Fichier rapport de pose"),

                        new OA\Property(property: "dateRapportDepose", type: "string", format: "date", description: "[etape_7] Date du rapport de dépose"),
                        new OA\Property(property: "commentaireRapportDepose", type: "string", description: "[etape_7] Commentaire dépose"),
                        new OA\Property(property: "rapportDepose", type: "string", format: "binary", description: "[etape_7] Fichier rapport de dépose"),

                        new OA\Property(property: "dateFinalisation", type: "string", format: "date", description: "[etape_8] Date de finalisation"),
                        new OA\Property(property: "commentaireFinalisation", type: "string", description: "[etape_8] Commentaire finalisation"),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Étape validée avec succès — retourne l'objet AvecImpression mis à jour",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    description: "Objet AvecImpression mis à jour",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 2),
                        new OA\Property(property: "etape", type: "string", description: "Étape suivante (après la transition)", example: "etape_2"),
                        new OA\Property(property: "dateEnvoiVisuel", type: "string", format: "date-time", nullable: true, example: "2025-01-20T00:00:00+00:00"),
                        new OA\Property(property: "commentaireEnvoiVisuel", type: "string", nullable: true),
                        new OA\Property(property: "envoiVisuel", type: "object", nullable: true, properties: [
                            new OA\Property(property: "path", type: "string", example: "media_deeps/visuel.jpg"),
                        ]),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Commande non trouvée ou erreur interne",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Erreur : Commande non trouvée"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'validation')]
    public function validationAvecImpression(Request $request, FaceRepository $faceRepository, AvecImpressionRepository $avecImpressionRepository, CommandeRepository $commandeRepository): Response
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

            $filePrefix = str_slug('document_01');
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

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
                    $this->updateFile($request->files->get('rapportPose'), $filePath, $filePrefix, function ($fichier) use ($avecImpression) {
                        $avecImpression->setRapportPose($fichier);
                    });
                    $avecImpression->setEtape("etape_7");
                    break;

                case 'etape_7':
                    $avecImpression->setDateRapportDepose(new \DateTime($request->get('dateRapportDepose')));
                    $avecImpression->setCommentaireRapportDepose($request->get('commentaireRapportDepose'));
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
                        $faceRepository->add($face, true);
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
    #[OA\Post(
        summary: "Avancer le workflow d'une commande SANS impression (5 étapes)",
        description: "Permet de faire progresser le traitement d'une commande sans impression (avec bâche client). Chaque appel avance l'étape d'une unité.\n\n| Étape envoyée | Champs requis | Passe à |\n|---|---|---|\n| `etape_1` | dateEnvoiBache, commentaireEnvoiBache, visualBache (fichier) | etape_2 |\n| `etape_2` | dateProgrammationPose, commentaireProgrammationPose | etape_3 |\n| `etape_3` | dateRapportPose, commentRapportPose, rapportPose (fichier) | etape_4 |\n| `etape_4` | dateRapportDepose, commentaireRapportDepose, rapportDepose (fichier) | etape_5 |\n| `etape_5` | dateFinalisation, commentaireFinalisation | → commande clôturée |",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ['commandeId', 'etape', 'userUpdate'],
                    properties: [
                        new OA\Property(property: "commandeId", type: "integer", description: "ID de la commande", example: 5),
                        new OA\Property(property: "etape", type: "string", description: "Étape actuelle à valider", enum: ["etape_1", "etape_2", "etape_3", "etape_4", "etape_5"], example: "etape_1"),
                        new OA\Property(property: "userUpdate", type: "integer", description: "ID de l'utilisateur qui effectue l'action", example: 1),

                        new OA\Property(property: "dateEnvoiBache", type: "string", format: "date", description: "[etape_1] Date d'envoi de la bâche", example: "2025-01-20"),
                        new OA\Property(property: "commentaireEnvoiBache", type: "string", description: "[etape_1] Commentaire sur la bâche"),
                        new OA\Property(property: "visualBache", type: "string", format: "binary", description: "[etape_1] Fichier visuel de la bâche"),

                        new OA\Property(property: "dateProgrammationPose", type: "string", format: "date", description: "[etape_2] Date de programmation de la pose"),
                        new OA\Property(property: "commentaireProgrammationPose", type: "string", description: "[etape_2] Commentaire"),

                        new OA\Property(property: "dateRapportPose", type: "string", format: "date", description: "[etape_3] Date du rapport de pose"),
                        new OA\Property(property: "commentRapportPose", type: "string", description: "[etape_3] Commentaire rapport pose"),
                        new OA\Property(property: "rapportPose", type: "string", format: "binary", description: "[etape_3] Fichier rapport de pose"),

                        new OA\Property(property: "dateRapportDepose", type: "string", format: "date", description: "[etape_4] Date du rapport de dépose"),
                        new OA\Property(property: "commentaireRapportDepose", type: "string", description: "[etape_4] Commentaire"),
                        new OA\Property(property: "rapportDepose", type: "string", format: "binary", description: "[etape_4] Fichier rapport de dépose"),

                        new OA\Property(property: "dateFinalisation", type: "string", format: "date", description: "[etape_5] Date de finalisation"),
                        new OA\Property(property: "commentaireFinalisation", type: "string", description: "[etape_5] Commentaire finalisation"),
                    ],
                    type: "object"
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Étape validée avec succès — retourne l'objet SansImpression mis à jour",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "code", type: "integer", example: 200),
                new OA\Property(property: "message", type: "string", example: "Operation effectuée avec succes"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    description: "Objet SansImpression mis à jour",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 3),
                        new OA\Property(property: "etape", type: "string", description: "Étape suivante (après la transition)", example: "etape_2"),
                        new OA\Property(property: "dateEnvoiBache", type: "string", format: "date-time", nullable: true, example: "2025-01-20T00:00:00+00:00"),
                        new OA\Property(property: "visualBache", type: "object", nullable: true, properties: [
                            new OA\Property(property: "path", type: "string", example: "media_deeps/bache.jpg"),
                        ]),
                    ]
                ),
                new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"), example: []),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Commande non trouvée ou erreur interne",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", nullable: true, example: null),
                new OA\Property(property: "message", type: "string", example: "Erreur : Commande non trouvée"),
                new OA\Property(property: "status", type: "integer", example: 200),
            ]
        )
    )]
    #[OA\Tag(name: 'validation')]
    public function sansImpression(Request $request, FaceRepository $faceRepository, SansImpressionRepository $sansImpressionRepository, CommandeRepository $commandeRepository): Response
    {
        try {
            $commandeId = $request->get('commandeId');
            $etape = $request->get('etape');
            $userUpdateId = $request->get('userUpdate');
            $commande = $commandeRepository->find($commandeId);
            $sansImpression = $commande->getSansImpression();

            $filePrefix = str_slug('document_01');
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            if (!$sansImpression) {
                throw new \Exception('Commande non trouvée');
            }

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
                    $sansImpression->setCommentaireProgrammationpose($request->get('commentaireProgrammationPose'));
                    $sansImpression->setEtape("etape_3");
                    break;

                case 'etape_3':
                    $sansImpression->setDateRapportPose(new \DateTime($request->get('dateRapportPose')));
                    $sansImpression->setCommentRapportPose($request->get('commentRapportPose'));
                    $this->updateFile($request->files->get('rapportPose'), $filePath, $filePrefix, function ($fichier) use ($sansImpression) {
                        $sansImpression->setRapportPose($fichier);
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
                        $faceRepository->add($face, true);
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
