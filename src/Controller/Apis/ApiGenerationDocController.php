<?php

namespace App\Controller\Apis;

use App\Service\ExcelGeneratorService;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Section;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;

class ApiGenerationDocController extends AbstractController
{
    

   

    private function fillFacesTable(Table $table, array $faces): void
    {
        foreach ($faces as $face) {
            $row = $table->addRow();
            $row->addCell(1250)->addText($face['site_number'] ?? '', [], ['align' => 'center']);
            $row->addCell(2000)->addText($face['emplacement'] ?? '', [], ['align' => 'center']);
            $row->addCell(1250)->addText($face['eclairage'] ?? '', [], ['align' => 'center']);
            $row->addCell(1250)->addText($face['format'] ?? '', [], ['align' => 'center']);
            $row->addCell(1250)->addText($face['date_debut'] ?? '', [], ['align' => 'center']);
            $row->addCell(1250)->addText($face['date_fin'] ?? '', [], ['align' => 'center']);
            $row->addCell(1750)->addText($face['location'] ?? '', [], ['align' => 'center']);
        }
    }

   

    #[Route('/api/generate-contrat2', name: 'generate_contrat2', methods: ['POST'])]
    #[OA\Post(
        path: "/api/generate-contrat2",
        summary: "Générer un contrat commercial",
        description: "Génère un fichier DOCX de contrat commercial basé sur les données fournies.",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du contrat à générer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "numero_contrat", type: "string", example: "GIVC-20241201-001"),
                    new OA\Property(property: "version", type: "string", example: "v1"),
                    new OA\Property(property: "commercial", type: "string", example: "Jean Dupont"),
                    new OA\Property(property: "client", type: "string", example: "Société ABC"),
                    new OA\Property(property: "contact_person", type: "string", example: "M. Martin Directeur"),
                    new OA\Property(property: "address", type: "string", example: "Rue du Commerce, Abidjan Plateau"),
                    new OA\Property(property: "postal_address", type: "string", example: "BP 1234 Abidjan 01"),
                    new OA\Property(property: "phone", type: "string", example: "+225 01 02 03 04 05"),
                    new OA\Property(property: "email", type: "string", example: "contact@societe-abc.ci"),
                    new OA\Property(property: "marque", type: "string", example: "Marque Excellence"),
                    new OA\Property(
                        property: "faces",
                        type: "array",
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "site_number", type: "string", example: "SITE001"),
                                new OA\Property(property: "emplacement", type: "string", example: "Abidjan Plateau - Carrefour de la République"),
                                new OA\Property(property: "eclairage", type: "string", example: "Oui"),
                                new OA\Property(property: "format", type: "string", example: "4x3m"),
                                new OA\Property(property: "date_debut", type: "string", format: "date", example: "2024-01-01"),
                                new OA\Property(property: "date_fin", type: "string", format: "date", example: "2024-12-31"),
                                new OA\Property(property: "location", type: "string", example: "CFA 500,000")
                            ]
                        )
                    ),
                    new OA\Property(property: "location_mensuelle", type: "string", example: "CFA 800,000"),
                    new OA\Property(property: "montant_total", type: "string", example: "CFA 9,600,000")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Fichier DOCX généré avec succès",
                content: new OA\MediaType(
                    mediaType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                    schema: new OA\Schema(type: "string", format: "binary")
                )
            )
        ]
    )]
    #[OA\Tag(name: 'document')]
    public function generateContrat2(Request $request): Response
    {

        $data = json_decode($request->getContent(), true);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop'    => Converter::cmToTwip(1),
            'marginBottom' => Converter::cmToTwip(1),
            'marginLeft'   => Converter::cmToTwip(1),
            'marginRight'  => Converter::cmToTwip(1),
        ]);

        // === HEADER avec Logo et Infos Contrat ===
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $headerTable->addRow();
        $headerTable->addCell(5000)
            ->addImage(
                __DIR__ . '/../../../public/logo.png',
                ['width' => 100, 'height' => 60, 'alignment' => 'left']
            );
        $cell = $headerTable->addCell(2500, ['borderSize' => 0, 'borderLeftColor' => '000000']);
        $textrun = $cell->addTextRun(['alignment' => 'center']);
        $textrun->addTextBreak();
        $textrun->addText("CONTRAT DE PUBLICITE", [
            'bold' => true,
            'size' => 18,
            'allCaps' => true,
            'borderLeftSize' => 0,
            'borderBottomColor' => '000000',
        ]);

        $cellRight = $headerTable->addCell(2500, [
            'borderTopSize' => 0,
            'borderLeftSize' => 0,
            'borderRightSize' => 0,
        ]);

        $cellRight->addText("Numéro de contrat:", ['bold' => true], ['align' => 'center']);
        $cellRight->addText("GIVC v1", ['bold' => true], ['align' => 'center', 'borderBottomSize' => 6, 'borderBottomColor' => '000000']);
        $cellRight->addText("Commercial :", [], ['align' => 'center']);

        // === Infos Client ===
        $clientTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'width' => 100 * 50, 'unit' => 'pct']);

        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("Client:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("A l’attention de:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("Adresse:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("Adresse postale:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("Tel.:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText(" Email:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("Client:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000, ['bgColor' => '003366'])->addText("Marque:", ['bold' => true, 'color' => 'FFFFFF', 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");
        $clientTable->addRow();
        $clientTable->addCell(1000,)->addText("Faces : ", ['bold' => true, 'size' => 7.5]);
        $clientTable->addCell(9000)->addText("");

        // === Tableau Emplacement ===
        $table =  $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'width' => 100 * 50, 'unit' => 'pct', 'alignment' => 'center']);

        $headers = ["N° de site", "Emplacement", "Eclairage", "Format", "Date début", "Date fin", "Location"];
        $table->addRow();
        $table->addCell(1250, ['size' => 8])->addText('N° de site', ['bold' => true], ['align' => 'center']);
        $table->addCell(2000, ['size' => 8])->addText('Emplacement', ['bold' => true], ['align' => 'center']);
        $table->addCell(1250, ['size' => 8])->addText('Eclairage', ['bold' => true], ['align' => 'center']);
        $table->addCell(1250, ['size' => 8])->addText('Format', ['bold' => true], ['align' => 'center']);
        $table->addCell(1250, ['size' => 8])->addText('Date début', ['bold' => true], ['align' => 'center']);
        $table->addCell(1250, ['size' => 8])->addText('Date fin', ['bold' => true], ['align' => 'center']);
        $table->addCell(1750, ['size' => 8])->addText('Location', ['bold' => true], ['align' => 'center']);



        // Ligne vide
        $this->fillFacesTable($table, $data['faces']);

        /*  $table->addRow();
        foreach ($headers as $_) {
            $table->addCell()->addText("");
        } */
        $tablefrea = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'width' => 100 * 50, 'unit' => 'pct', 'alignment' => 'center']);

        $tablefrea->addRow();
        $tablefrea->addCell(1500,)->addText( count($data['faces']) . " Face(s)", ['bold' => true, 'size' => 7.5]);
        $tablefrea->addCell(8500)->addText("");

        // === Totaux ===
        $tableTotal = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'width' => 100 * 50, 'unit' => 'pct', 'alignment' => 'center']);

        $tableTotal->addRow();
        $tableTotal->addCell(8250)->addText("LOCATION MENSUELLE HT", ['bold' => true]);
        $tableTotal->addCell(1750)->addText("CFA.00", ['bold' => true], ['align' => 'right']);

        $tableTotal->addRow();
        $tableTotal->addCell(8250)->addText("LOCATION MENSUELLE TOTAL HT", ['bold' => true]);
        $tableTotal->addCell(1750)->addText("CFA.00", ['bold' => true,], ['align' => 'right']);

        // === Conditions de vente ===
        $tableCond = $section->addTable([
            'borderSize' => 0,
            'width' => 100 * 50,
            'unit' => 'pct',
            'alignment' => 'center'
        ]);

        $tableCond->addRow();
        $tableCond->addCell(10000, ['bgColor' => '003366'])->addText("Conditions de vente", ['bold' => true, 'color' => 'FFFFFF', 'size' => 10], ['align' => 'left']);

        $tableCond->addRow();
        $cellCond = $tableCond->addCell(10000);

        $conditionsText =
            "1. SOMMAIRE DES CONDITIONS GENERALES DE VENTE\n" .
            "   Tous les loyers sont payables en F.CFA et non indexés sur le cours d’une autre monnaie. " .
            "Les paiements effectués dans une monnaie autre que l’Euro seront calculés suivant un taux de conversion de monnaie appliqué par le Contractant à la date du paiement. " .
            "Les prix du contrat sont libellés en francs CFA et convenus à une parité fixe actuelle avec l’euro de 655,957 F CFA pour 1 Euro. " .
            "Si cette parité venait à varier de plus de 5% en plus ou en moins, les prix en F CFA seront réajustés.\n\n" .

            "2. Tous les prix sont spécifiés hors TVA et autres taxes publicitaires ; la TVA et les autres taxes seront appliquées au taux en vigueur au jour de facturation. " .
            "Toute variation de TVA ou addition de quelque taxe que ce soit influera sur la valeur du contrat, à partir de la mise en application de cette nouvelle taxe avec ajout de la pleine valeur d’une telle variation.\n\n" .

            "3. Voir date de début telle qu’elle est définie dans les clauses 1.5, 4.1, et 5.1 / Voir la clause 5.2 pour l’escalade / Voir la limitation de marques dans la clause 2.6";

        $cellCond->addText($conditionsText, [], ['align' => 'left']);

        $section->addText("NB: VOIR CI-JOINT POUR LES CONDITIONS DE VENTE. ");

        // === Bloc Signature ===
        $signatureTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);

        $signatureTable->addRow();
        $signatureTable->addCell(1000, ['bgColor' => '003366'])->addText("Signature de l’Agence", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(2500)->addText("", ['bold' => true]);
        $signatureTable->addCell(1000, ['bgColor' => '003366'])->addText("Signature de l’Annonceur/Client", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(2500)->addText("", ['bold' => true]);
        $signatureTable->addCell(1500, ['bgColor' => '003366'])->addText("Signature de Global Cote d’Ivoire", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(1500)->addText("", ['bold' => true]);
        $signatureTable->addRow();
        $signatureTable->addCell(1000, ['bgColor' => '003366'])->addText("Nom et Fonction", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(2500)->addText("", ['bold' => true]);
        $signatureTable->addCell(1000, ['bgColor' => '003366'])->addText("Nom et Fonction", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(2500)->addText("", ['bold' => true]);
        $signatureTable->addCell(1500, ['bgColor' => '003366'])->addText("Nom et Fonction", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(1500)->addText("Mme Claire MALTHET\nDG", ['bold' => true]);
        $signatureTable->addRow();
        $signatureTable->addCell(1000, ['bgColor' => '003366'])->addText("Date", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(2500)->addText("", ['bold' => true]);
        $signatureTable->addCell(1000, ['bgColor' => '003366'])->addText("Date", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(2500)->addText("", ['bold' => true]);
        $signatureTable->addCell(1500, ['bgColor' => '003366'])->addText("Date", ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['align' => 'center']);
        $signatureTable->addCell(1500)->addText("", ['bold' => true]);

        $section->addTextBreak(1);

        // === Section principale en 2 colonnes ===
        $section = $phpWord->addSection([
            'colsNum'   => 2,
            'colsSpace' => 400,
            'marginTop'    => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(1),
            'marginLeft'   => Converter::cmToTwip(1),
            'marginRight'  => Converter::cmToTwip(1),
        ]);

        // === Ajout du header (titre bleu) ===
        $header = $section->addHeader();
        $tableHeader = $header->addTable(['borderSize' => 0, 'width' => 100 * 50, 'unit' => 'pct']);
        $tableHeader->addRow();
        $tableHeader->addCell(10000, ['bgColor' => '003366'])
            ->addText(
                "CONDITIONS GENERALES DE VENTE DU CONTRAT",
                ['bold' => true, 'color' => 'FFFFFF'],
                ['align' => 'center']
            );

        // === Bloc 1 : DEFINITIONS ===
        $section->addText("1. DEFINITIONS", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "Dans ce contrat, les expressions suivantes ont les significations qui leur sont attribuées plus bas et les expressions ont les significations correspondantes :

1.1 Par « Agence ou Annonceur » on entendra la personne, l’entreprise ou la société qui commande au Contractant le placement du visuel publicitaire par la location d’espace publicitaire, et cela devra comprendre le successeur en titre du de l’Agence ou de l’Annonceur ;   
1.2 Le « visuel » comprendra les affiches et autres matériels destinés à être exposés (pour lesquels on emploie communément le terme « affichage ») par le Contractant sur les panneaux ; 
1.3 Par « Contractant » on entendra Global Outdoor Systems, suivant la description que porte le bon de commande et cela comprendra les filiales de l’Entrepreneur, ses successeurs en titre et ses attributaires ou voudra dire la société à qui est passée une commande pour un affichage de visuel publicitaire ;
1.4 Par « affichage » on entendra l’affichage de visuels publicitaires sur les panneaux par le Contractant, de la part du Publicitaire ;
1.5 Par « date de départ » on entendra la date qui avait été convenue,
1.6 Par « mois » on entendra un mois calendaire ;
1.7 Par « jour ouvrable » on entendra n’importe quel jour de la semaine, du lundi au vendredi inclus, hormis tout jour férié dans le pays où le visuel publicitaire est affiché
",
            ['size' => 10],
            ['align' => 'both']
        );

        // === Bloc 2 : CONDITIONS GENERALES COMMERCIALES ===
        $section->addTextBreak(1);
        $section->addText("2. CONDITIONS GENERALES COMMERCIALES", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "2.1. L’Agence ou l’Annonceur déclare solennellement qu’il loue au Contractant l’espace publicitaire spécifié dans le bon de commande, y compris tout espace Publicitaire de remplacement validé au préalable par le Publicitaire pour la période spécifiée sur le bon de commande (et toute prolongation s’y rapportant) au moment du paiement des loyers établis ci-dedans par le Contractant a l’agence ou à l’annonceur.
2.2. Tous les visuels publicitaires sont placés et annoncés sous réserve de ces conditions générales, y compris toutes annexes concernées, faisant partie du contrat établi entre le Contractant et l’Agence ou l’Annonceur, et qui découlent de commandes pour l’affichage de visuels publicitaires acceptées par le Contractant.
2.3. L’Agence ou l’Annonceur accepte de payer le Contractant ponctuellement, tel qu’il est spécifié dans le présent document et dans le bon de commande, sans déduction ou compensation, et sans frais bancaires ou de change.
2.4. Aucune autre condition générale que ces conditions générales ou toute modification s’y rapportant (suivant la clause 9 ci-dessous), n’engagera le Contractant, à moins qu’elle n’ait été émise par écrit ; mais rien n’interdira au Contractant ou à L’Agence ou l’Annonceur de faire varier ces termes et/ou conditions générales s’ils se sont mutuellement mis d’accord pour le faire, l’ont mis par écrit et les deux parties ont dûment signé.
2.5. L’espace sur le panneau réservé ne sera utilisé que pour le visuel publicitaire, spécifié dans le bon de commande et ne peut comporter aucun contenu politique, ou contraire à la législation locale en vigueur.
2.6. La livraison du visuel de l’Agence ou l’Annonceur sera effectif lorsque les exigences de la clause 3 seront respectées, et lorsque les instructions d’affichage seront données au Contractant.
2.7. L’affichage de tout visuel publicitaire est toujours soumis à sa disponibilité et/ou au respect de la part des deux parties des exigences légales ou autres.
2.8. Il est convenu que chaque paragraphe, chaque clause et alinéa, chaque obligation et droit des parties, chaque dispense, chaque disposition dans ce contrat sont séparables les uns des autres.
2.9. S’il se trouve qu’un paragraphe, une clause, une obligation, une dispense, une légalisation ou un droit est incomplet ou non exécutable pour une quelconque raison par tout tribunal compétent, les autres paragraphes, clauses, alinéas, obligations, dispenses, légalisations et droits continuent d’être pleinement en vigueur.

",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );

        // === Bloc 3 : RESPONSABILITES ===
        $section->addTextBreak(1);
        $section->addText("3. RESPONSABILITES DE L’AGENCE OU DE L’ANNONCEUR", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "3.1 Tout visuel publicitaire (affiches imprimées ou fichiers électroniques dans le cas d’un affichage sur panneau électronique) sera livré, franco de port à l’adresse ou aux adresses d’expédition de l’entrepôt spécifiées par le contractant dans un délais de 5 jours ouvrables avant la date prévue pour l’affichage et au plus tard 48 heures avant la date prévue d’affichage sauf accord préalable agrée  entre les parties. 
3.2 L’Agence ou l’Annonceur s’engage à aider le Contractant autant que possible et à fournir rapidement toute information et assistance qui seront nécessaires, y compris la conception, l’illustration et/ou les affiches, qu’il devra fournir au publicitaire, de manière à faciliter la production ou la fabrication rapide de la pose du visuel publicitaire dans la limite de la/des dates spécifiées.
3.3 Les illustrations pour un visuel publicitaire qui doivent être imprimés devront être livrées avant une date sur laquelle le Contractant et le Publicitaire se seront mis d’accord. L’Agence ou l’Annonceur devra assumer tous les coûts de production, de fabrication, de transport, d’affichage et de désaffichage à la fin de la campagne. Ces coûts seront payables au contractant à la demande et sans déduction ou dédommagement, ainsi que libres de frais bancaires ou de change entre monnaies.
3.4 Si ce contrat de location est conclu par une Agence agissant pour le compte d’un Annonceur tiers. L’Agence est entièrement et légalement responsable du paiement total des factures liées à ce contrat dans les délais et conditions prévues dans l’article 6. La même condition sera applicable dans le cas d’émission de bons de commandes séparés.  
3.5. L’Agence ne pourra en aucun cas se prévaloir d’un défaut ou retard de paiement de l’annonceur tiers pour justifier d’un quelconque retard de paiement des factures liées à ce contrat et aux éventuels bons de commandes séparés. 

",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 4 : RESPONSABILITES DU CONTRACTANT  ===
        $section->addTextBreak(1);
        $section->addText("4. RESPONSABILITES DU CONTRACTANT ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "4.1 Le Contractant s’engage à afficher la campagne à une date s’approchant au plus près de dix jours maximums de la date de départ spécifiée dans le formulaire de commande. Le Publicitaire accepte que le respect strict de cette date ne puisse être possible, et le Contractant est autorisé à poursuivre ses obligations de date au plus près des instructions données par le Publicitaire. Lorsqu’aucune date de début n’aura été spécifiée, les frais de loyer pour l’affichage d’un visuel publicitaire partiront de la date de début du mois du formulaire de commande.
             4.2 Tous les loyers incluent le maintien de l’affichage en bon état, à la condition que le publicitaire ait fournit au contractant des affiches de remplacement, et ou telles qu’elles aient été demandées par le contractant.
             4.3 Le Contractant se réserve le droit de déplacer une affiche vers un autre site offrant une exposition similaire dans le cas de la perte d’un site ou de réduction d’espaces, ou sur une quelconque autre base légitime. Ceci sera soumis à l’approbation du Publicitaire, faute de quoi l’affiche sera retirée du service et la responsabilité du Publicitaire cessera de ce fait dès le retrait de l’affiche.
             4.4 Le Contractant accepte pleinement toute responsabilité concernant le respect des exigences statutaires et légales relatif à l’usage et à la maintenance de tout site destiné à la pose du visuel publicitaire auquel ce contrat se réfère.
            4.5 Il sera de la responsabilité du Contractant d’obtenir les permis et autorisations auprès du gouvernement et/ou d’autres autorités, afin de placer les panneaux/et d’afficher le visuel publicitaire dans tous les lieux demandés. Le Contractant aura également la responsabilité de tous les paiements à sa charge ou imputables à l’Agence ou l’Annonceur, des coûts, taxes et autres dus à quelque autorité compétente que ce soit pour ce qui résulte de l’installation et/ou le placement des panneaux/l’affichage des visuels publicitaires. L’Agence ou l’Annonceur n’aura en aucun cas la responsabilité de régler directement aucun paiement, coûts, taxes ou autre à aucune autorité compétente, s’ils résultent de l’installation et /ou du placement de panneaux/de la pose du visuel publicitaire dans quelque lieu que ce soit.

",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 5 : DUREE  ===
        $section->addTextBreak(1);
        $section->addText("5. DUREE ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "5.1 Ce contrat prend effet et se termine à compter de la date indiquée à la page 1. ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 6 : COMPTES  ===
        $section->addTextBreak(1);
        $section->addText("6. COMPTES ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "5.1 Ce contrat prend effet et se termine à compter de la date indiquée à la page 1. ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 6 : COMPTES  ===
        $section->addTextBreak(1);
        $section->addText("6. COMPTES ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "6.1 A la signature du présent contrat, 50% (cinquante pourcent) du montant total du loyer est dû d’avance, à la suite de quoi les paiements interviendront, par avance, par cycle récurrent de trois mois.
6.2 En cas de non-paiement ou quelque autre manquement aux engagements énumérés dans le présent contrat de la part d’une des parties, l’autre partie sera autorisée mais pas obligé et sans préjudice d’autres droits auxquels la loi l’autoriserait, à suspendre ou annuler ce contrat.
6.3 En cas d’annulation ou de suspension envisagée dans la clause 6.2, l’Agence ou l’Annonceur sera immédiatement responsable du paiement du montant dû jusqu’à la date d’annulation ou de suspension, suivant le cas, et aura immédiatement la responsabilité de payer 75% (soixante-quinze pourcent) de la valeur totale de la portion incomplète du contrat. Ce dernier montant prend en compte, entre autres, les montants payables légalement par le Contractant aux propriétaires de sites pendant la portion incomplète du contrat, la perte de bénéfice et l’inévitable retard éventuel pour relouer l’espace et représente une authentique pré estimation par les parties des dommages et intérêts liquidés qui seront subis par le Contractant à la suspension et/ou l’annulation, ainsi qu’il est envisagé dans le présent contrat. Le publicitaire devra, en cas d’annulation envisagée, faire parvenir au contractant un préavis tel que défini dans l’article 6.3 
L’Agence ou l’Annonceur devra, en cas d’annulation envisagée, faire parvenir au Contractant un préavis :
•	Pour un contrat initial entre 01 et 03 mois : un préavis de 01 mois
•	Pour un contrat initial entre 06 et 12 mois : un préavis de 03 mois
•	Pour un contrat initial de plus de 12 mois : un préavis de 03 mois
Si le client annule sans avoir transmis de préavis, il sera redevable de payer 75% de la valeur totale restante du contrat.

 ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 7 : GARANTIES, RESPONSABILITES ET INDEMNITES  ===
        $section->addTextBreak(1);
        $section->addText("7. GARANTIES, RESPONSABILITES ET INDEMNITES ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "7.1. L’Agence ou l’Annonceur garantit que et s’engage à ce que :
7.1.1. Tout visuel publicitaire sera conforme à toute exigence statutaire et légale dans le pays en vigueur 
7.1.2. Toutes les licences et autorisations pour l’affichage d’un quelconque matériel de publicité ou de droit d’auteur contenu ou l’apparence de quiconque dans l’affiche publicitaire, seront obtenues avant de remettre ce matériel au Contracteur.
7.1.3 Aucune affiche publicitaire ne violera le droit d’auteur ou d’autres droits ni ne sera diffamatoire vis-à-vis de ou contre un tiers.
7.2 Le Contractant et le propriétaire de site sont de ce fait protégés par l’Agence ou l’Annonceur contre toutes les actions, procédures, pénalités, créances, revendications et/ou responsabilités, tous les coûts, dommages, frais découlant de la rupture d’une des garanties mentionnées ci-dessus ou qui pourraient survenir de quelque manière que ce soit, comme conséquence de l’usage d’une affiche publicitaire ou d’un matériel quelconque fourni par le publicitaire ou affiché pour lui ou son mandant.
7.3 Le Contractant aura le droit de refuser d’afficher ou de continuer d’afficher des visuels publicitaires qui ne respectent pas en tous points les garanties et les engagements du Publicitaire énoncées dans la clause 7.1 ci-dessus.
7.4. Si pour des raisons d’impossibilité d’exécution de la part du Contractant pour les raisons suivantes : force majeure, grèves, émeutes et mouvements populaires, l’Agence ou l’Annonceur  suspend, change ou annule ce contrat, ou si cela se fait pour toute autre raison en-dehors de la volonté du Contractant, l’Agence ou l’Annonceur  aura la responsabilité de payer au Contractant toutes les sommes dues et à devoir par l’Agence ou l’Annonceur au Contractant pour la période allant jusqu’à cette suspension, ce changement ou cette annulation. L’Agence ou l’Annonceur aura également la 
responsabilité des frais envisagés dans la clause 3.4 ci-dessus, en ce qui concerne la période restante du contrat.
7.5. Si le Contractant est responsable du non-affichage ou de l’affichage endommagé ou erroné d’un quelconque visuel publicitaire, la responsabilité du Contractant ne sera pas supérieure aux frais de loyer pour l’affichage de ce visuel publicitaire pendant la période de non-affichage ou d’affichage endommagé ou erroné.
7.6. Le Contractant ne sera responsable ni de la perte ni des dommages d’une quelconque affiche lui ayant été fournie.
7.7. Toute affiche ou autres matériels de publicité en la possession du Contractant qui sont en excédent des exigences ou ont été retirés de l’affichage, ne seront pas gardés au-delà de 10 (dix) jours après la fin de la période d’affichage et pourront alors être détruits, à moins que le Publicitaire n’ait averti par écrit qu’ils doivent être gardés à des fins de collection.
7.8 Dans le cas où un panneau d’affichage loue avec éclairage n'est pas éclairé ou subit une perte d'alimentation électrique ou toute autre restriction empêchant l'éclairage du Support, le Contractant est tenu de rembourser le Publicitaire proportionnellement pour la période d’interruption de l’éclairage, à hauteur de 15 % du montant de la location du site. Dans le cas où un Support deviendrait totalement inopérant en raison d'une panne d'électricité, le support est mis hors service pour la durée de cette panne, après quoi l’obligation du Publicitaire au titre de la location de ce Support sera suspendue jusqu'à ce que le support soit remis en service.

 ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 8 : FAILLITE ETC  ===
        $section->addTextBreak(1);
        $section->addText("8. FAILLITE ETC ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "Si le client fait faillite ou commet un acte de faillite ou fait une cession au profit de ses créanciers ou tente de le faire, ou étant une société doit aller ou être mis en liquidation ou tenter de le faire, ou, si un séquestre sera désigné pour tout ou partie de l'engagement du client ou, s'il y a une violation substantielle par le client du présent contrat de location publicitaire manifestant son intention de ne pas honorer ses obligations en vertu du présent contrat de location publicitaire, il sera légal pour le contractant, par notification écrite au client, de résilier le contrat de location publicitaire sans préjudice de tout droit d'action ou de recours du contractant alors existant , et pour supprimer toute affiche ou diffusion publicitaire de tous les sites qui font l'objet du contrat de location publicitaire (ou de tout renouvellement de celui-ci), immédiatement et sans préavis au client, et réclamer des dommages-intérêts comme indiqué dans les présentes et permis par toute loi, et aura le droit d’afficher sur ces sites en question toute autre annonce pour le compte  de tout autre client de l'entrepreneur. ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 9 :   CHANGEMENT DE TARIFS  ===
        $section->addTextBreak(1);
        $section->addText("9.  CHANGEMENT DE TARIFS ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "9.1. Le Contractant se réserve le droit de changer les tarifs de ses loyers et/ou n’importe laquelle de ces conditions générales après un préavis par écrit de 3 (3) mois (« Le préavis d’amendement ») adressé à l’Agence ou à l’Annonceur.  L’Agence ou l’Annonceur sera autorisé, en envoyant un préavis par écrit au Contractant dans la limite de 1 (un) mois suivant le préavis d’amendement, à modifier toute commande pour un affichage publicitaire auquel se rapporterait le changement de tarifs ou de conditions générales, après avoir réglé toutes les sommes encore dues et à devoir au Contractant. Le préavis d’amendement, en ce qui concerne un contrat couvrant plusieurs sites évalués individuellement, devra comporter les détails du changement pour chacun des sites couverts par ce contrat.

9.2. En cas de changements de taxes sur la publicité, TVA et autres couts et taxes imputables au compte de l’Agence ou de l’Annonceur, le Contractant informera immédiatement l’Agence ou l’Annonceur et les nouveaux taux seront applicables immédiatement à compter de la date annoncée par les autorités de tutelle.    
 ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 10 :   JURIDICTION  ===
        $section->addTextBreak(1);
        $section->addText("10.  JURIDICTION ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "10.1 Le présent contrat est soumis à la loi ivoirienne.  
10.2 Tout différent portant sur l’interprétation du présent contrat, qui n’aura pu faire l’objet d’une solution amiable dans un délai d’un mois (01) suivant sa survenance, sera soumis au tribunal de 1ère instance d’Abidjan par la partie la plus diligente. 
 ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        // === Bloc 11 :   DROITS D’ENREGISTREMENT ET DE TIMBRES   ===
        $section->addTextBreak(1);
        $section->addText("11.  DROITS D’ENREGISTREMENT ET DE TIMBRES  ", ['bold' => true, 'underline' => 'single'], ['size' => 11]);
        $section->addText(
            "Tous les frais d’enregistrement et de timbres relatifs au présent contrat sont à la charge de l’Agence ou l’Annonceur qui s’y oblige.  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "FAIT A :  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "DATE:  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "Entreprise :  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "Nom  :  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "Fonction   :  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "Signature    :  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "Dûment autorisé par résolution de la société.
En accord avec les conditions générales standard du contractant  ",
            ['size' => 8],
            ['align' => 'both']
        );
        $section->addText(
            "FAIT A: Abidjan ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "GLOBAL OUTDOOR SYSTEMS",
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['align' => 'both']
        );
        $section->addText(
            "Nom : Mme Claire MALTHET",
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['align' => 'both']
        );

        $section->addText(
            "Signature    :  ",
            ['size' => 11],
            ['align' => 'both', 'name' => 'Times New Roman']
        );
        $section->addText(
            "Dûment autorisé par résolution de la société. ",
            ['size' => 8],
            ['align' => 'both']
        );


        // === Pied de page ===
        $footer = $section->addFooter();
        $footer->addText("Printed with OutTrack3 (v1) on : " . date('d/m/Y'), ['italic' => true, 'size' => 8], ['align' => 'right']);

        // === Sauvegarde en mémoire ===
        $fileName = "contrat_publicite_" . time() . ".docx";

        // Créer un writer et capturer la sortie dans une variable
        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        // Utiliser output buffering pour capturer le contenu binaire
        ob_start();
        $writer->save('php://output');
        $binaryContent = ob_get_clean();

        // Retourner la réponse avec le contenu binaire
        return new Response(
            $binaryContent,
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }



     #[Route('/api/generate-proforma', name: 'generate_proforma', methods: ['POST'])]
    #[OA\Post(
        path: "/api/generate-proforma",
        summary: "Générer une proforma de location de site sur proforma ",
        description: "Génère une proforma de location de site sur proforma.",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du contrat à générer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "numero", type: "string", example: "GIVC-20241201-001"),
                    new OA\Property(property: "client", type: "string", example: "Claire MALTHET"),
                    new OA\Property(property: "attn", type: "string", example: "Elody DARGA"),
                    new OA\Property(property: "date", type: "string", example: "2024-11-19"),
                    new OA\Property(property: "dureeMois", type: "string", example: "12"),
                    new OA\Property(property: "remisePourcentage", type: "string", example: "0.18"),
                    new OA\Property(property: "sites", type: "string", example: "AEROPORT DE KORHOGO - CONTROLE SECURITE"),
                    new OA\Property(property: "codeSite", type: "string", example: "IVC/AKH/018"),
                    new OA\Property(property: "dimensions", type: "string", example: "1,5 m X 3 m"),
                    new OA\Property(property: "prixMensuel", type: "string", example: "160000"),

                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Fichier DOCX généré avec succès",
                content: new OA\MediaType(
                    mediaType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                    schema: new OA\Schema(type: "string", format: "binary")
                )
            )
        ]
    )]
    #[OA\Tag(name: 'document')]
    public function generateProformaWithData(ExcelGeneratorService $excelGenerator, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $proformaData = [
            'numero' => $data['numero'] ?? '1911243',
            'client' => $data['client'] ?? 'Claire MALTHET',
            'attn' => $data['attn'] ?? 'Elody DARGA',
            'date' => new \DateTime($data['date'] ?? '2024-11-19'),
            'site' => $data['site'] ?? 'AEROPORT DE KORHOGO - CONTROLE SECURITE',
            'codeSite' => $data['codeSite'] ?? 'IVC/AKH/018',
            'dimensions' => $data['dimensions'] ?? '1,5 m X 3 m',
            'prixMensuel' => $data['prixMensuel'] ?? 190000,
            'dureeMois' => $data['dureeMois'] ?? 12,
            'remisePourcentage' => $data['remisePourcentage'] ?? 0.18
        ];
        
        $spreadsheet = $excelGenerator->generateProforma((array)$proformaData);
        
        return $excelGenerator->createResponse(
            $spreadsheet, 
            'Location ' . $proformaData['dureeMois'] . ' mois ' . 
            str_replace(' ', '_', $proformaData['site']) . '_' . 
            date('d m y') . '.xlsx'
        );
    }
}
