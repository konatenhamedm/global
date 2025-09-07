<?php
// src/Service/ExcelGeneratorService.php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelGeneratorService
{
    /* private $projectDir;
    
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    } */
    
    public function generateProforma($proforma)
    {
        $spreadsheet = new Spreadsheet();
        
        // Feuille "04 mois"
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('04 mois');
        
        // Ajouter le logo
        $this->addLogo($sheet);
        
        // En-tête
        $sheet->setCellValue('A7', "CÔTE D'IVOIRE\nTél : (+225) 27 21 34 05 38");
        $sheet->getStyle('A7')->getAlignment()->setWrapText(true);
        
        $sheet->setCellValue('A9', 'PROFORMA LOCATION DE SITE AEROPORT DE KORHOGO');
        $sheet->setCellValue('A10', 'Proforma N°' . $proforma['numero'] . ' / LOCATION');
        
        // Destinataire
        $sheet->setCellValue('B12', 'To');
        $sheet->setCellValue('C12', 'HOTEL LES SAVANES KORHOGO');
        $sheet->setCellValue('D12', $proforma['client']);
        
        $sheet->setCellValue('B13', 'Attn');
        $sheet->setCellValue('C13', $proforma['attn']);
        
        $sheet->setCellValue('B14', 'Date');
        $sheet->setCellValue('C14', $proforma['date']->format('Y-m-d'));
        
        $sheet->setCellValue('A16', 'Proforma N°' . $proforma['numero'] . ' / LOCATION');
        
        // Détails du site
        $sheet->setCellValue('C18', 'Descriptif de sites');
        $sheet->setCellValue('D18', 'F CFA');
        
        $sheet->setCellValue('C19', 'LOCATION DE SITE ' . $proforma['dureeMois'] . ' MOIS : du 01/01 au 31/12/2025');
        
        $siteDescription = $proforma['site'] . "\n" . $proforma['codeSite'] . "\nDimensions : " . $proforma['dimensions'];
        $sheet->setCellValue('C20', $siteDescription);
        $sheet->getStyle('C20')->getAlignment()->setWrapText(true);
        $sheet->setCellValue('D20', number_format($proforma['prixMensuel'], 0, ',', ' ') . ' F CFA / mois');
        
        // Calculs
        $sheet->setCellValue('C21', 'TOTAL HT LOCATION ' . $proforma['dureeMois'] . ' MOIS');
        $sheet->setCellValue('D21', '=(' . $proforma['prixMensuel'] . '*1)*' . $proforma['dureeMois']);
        
        $sheet->setCellValue('C22', 'Remise commerciale de ' . ($proforma['remisePourcentage'] * 100) . '%');
        $sheet->setCellValue('D22', '=D21*' . $proforma['remisePourcentage']);
        $sheet->setCellValue('H22', '=D22/' . $proforma['dureeMois']);
        
        $sheet->setCellValue('C23', 'TOTAL HT Après remise commerciale de ' . ($proforma['remisePourcentage'] * 100) . '%');
        $sheet->setCellValue('D23', '=D21-D22');
        
        $sheet->setCellValue('C24', 'TVA 18%');
        $sheet->setCellValue('D24', '=D23*18/100');
        
        $sheet->setCellValue('C25', 'TSP 3%');
        $sheet->setCellValue('D25', '=D23*3/100');
        
        $sheet->setCellValue('C26', 'GRAND TOTAL TTC');
        $sheet->setCellValue('D26', '=D23+D24+D25');
        
        // Règlement
        $sheet->setCellValue('C28', 'Règlement');
        $sheet->setCellValue('D28', 'Par chèque ou virement bancaire');
        
        $sheet->setCellValue('C29', 'Adresser svp votre BDC à l\'ordre de GLOBAL OUTDOOR SYSTEMS COTE D\'IVOIRE SA');
        
        // Conditions de règlement
        $sheet->setCellValue('B31', 'Conditions de règlement : Janvier : 1.131.108F, Avril : 377.036F, Juillet : 377.036F, Novembre : 377.036F');
        
        // Signature
        $sheet->setCellValue('B33', $proforma['client']);
        $sheet->setCellValue('B34', 'Directrice Générale');
        $sheet->setCellValue('B35', 'Global Outdoor Systems CI');
        
        // Pied de page
        $sheet->setCellValue('A47', "GLOBAL OUTDOOR SYSTEMS COTE D'IVOIRE SA\nImmeuble les Elfes, rue du Canal, Bietry, Abidjan - Côte d'Ivoire\nTél : (+225) 27 21 34 05 38\nRCCM 256760 – CC 0040848X");
        $sheet->getStyle('A47')->getAlignment()->setWrapText(true);
        
        // Style
        $this->applyStyles($sheet);
        
        // Feuille "sites"
        $sheetSites = $spreadsheet->createSheet();
        $sheetSites->setTitle('sites');
        
        $sheetSites->setCellValue('C3', 'SAN PEDRO - EMBARQUEMENT');
        $sheetSites->setCellValue('C4', 'IVC/ASP/010');
        $sheetSites->setCellValue('C5', 'Dimensions : 1,5 m X 3 m');
        
        $sheetSites->setCellValue('C7', 'BOUAKE - EMBARQUEMENT');
        $sheetSites->setCellValue('C8', 'IVC/ABKE/007');
        $sheetSites->setCellValue('C9', 'Dimensions : 1,2 m X 3 m');
        
        $sheetSites->setCellValue('C11', 'AEROPORT DE KORHOGO - EMBARQUEMENT');
        $sheetSites->setCellValue('C12', 'IVC/AKH/019');
        $sheetSites->setCellValue('C13', 'Dimensions : 2,5 m X 2 m');
        
        // Appliquer les styles à la feuille sites
        $this->applySitesStyles($sheetSites);
        
        // Retourner la feuille principale
        $spreadsheet->setActiveSheetIndex(0);
        
        return $spreadsheet;
    }
    
    private function addLogo($sheet)
    {
        // Chemin vers le logo (à adapter selon votre structure)
        //$logoPath = $this->projectDir . '/public/images/logo.png';
        $logoPath =  __DIR__ . '/../../public/logo.png';
        
        // Si le logo existe, l'ajouter
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        } else {
            // Alternative si pas de logo
            $sheet->setCellValue('A1', 'LOGO');
            $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        }
    }
    
    private function applyStyles($sheet)
    {
        // Styles pour les en-têtes
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        
        // Appliquer les styles
        $sheet->getStyle('A7')->applyFromArray($headerStyle);
        $sheet->getStyle('A9')->applyFromArray($titleStyle);
        $sheet->getStyle('A10')->applyFromArray($headerStyle);
        $sheet->getStyle('A16')->applyFromArray($headerStyle);
        $sheet->getStyle('C18:D18')->applyFromArray($headerStyle);
        
        // Bordures pour les cellules de données
        $borderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        
        $sheet->getStyle('C18:D18')->applyFromArray($borderStyle);
        $sheet->getStyle('C19:D19')->applyFromArray($borderStyle);
        $sheet->getStyle('C20:D20')->applyFromArray($borderStyle);
        $sheet->getStyle('C21:D21')->applyFromArray($borderStyle);
        $sheet->getStyle('C22:D22')->applyFromArray($borderStyle);
        $sheet->getStyle('C23:D23')->applyFromArray($borderStyle);
        $sheet->getStyle('C24:D24')->applyFromArray($borderStyle);
        $sheet->getStyle('C25:D25')->applyFromArray($borderStyle);
        $sheet->getStyle('C26:D26')->applyFromArray($borderStyle);
        
        // Bordures pour la colonne H
        $sheet->getStyle('H22')->applyFromArray($borderStyle);
        
        // Alignement des montants à droite
        $rightAlignment = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ];
        
        $sheet->getStyle('D18:D26')->applyFromArray($rightAlignment);
        $sheet->getStyle('H22')->applyFromArray($rightAlignment);
        
        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(5);
        $sheet->getColumnDimension('F')->setWidth(5);
        $sheet->getColumnDimension('G')->setWidth(5);
        $sheet->getColumnDimension('H')->setWidth(15);
        
        // Fusionner les cellules si nécessaire
        $sheet->mergeCells('A9:D9');
        $sheet->mergeCells('A10:D10');
        $sheet->mergeCells('A16:D16');
        
        // Hauteur de ligne pour les cellules avec texte multiligne
        $sheet->getRowDimension(7)->setRowHeight(30);
        $sheet->getRowDimension(20)->setRowHeight(40);
        $sheet->getRowDimension(47)->setRowHeight(60);
    }
    
    private function applySitesStyles($sheet)
    {
        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(50);
        
        // Styles pour les sites
        $siteHeaderStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        
        $sheet->getStyle('C3')->applyFromArray($siteHeaderStyle);
        $sheet->getStyle('C7')->applyFromArray($siteHeaderStyle);
        $sheet->getStyle('C11')->applyFromArray($siteHeaderStyle);
    }
    
    public function createResponse(Spreadsheet $spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        
        return $response;
    }
}