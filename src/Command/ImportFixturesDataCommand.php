<?php

namespace App\Command;

use App\Entity\Face;
use App\Entity\Fichier;
use App\Entity\Localite;
use App\Entity\Panneau;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:import-fixtures-data',
    description: 'Import fixtures data including real images for Panneau and Face.',
)]
class ImportFixturesDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->projectDir = $params->get('kernel.project_dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $localites = [
            'Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'Korhogo', 'San-Pédro',
            'Divo', 'Man', 'Gagnoa', 'Soubré', 'Agboville', 'Dabou', 'Grand-Bassam',
            'Bouaflé', 'Issia', 'Sinfra', 'Katiola', 'Bingerville', 'Adzopé',
            'Séguéla', 'Bondoukou', 'Oumé', 'Ferkessédougou', 'Dimbokro',
            'Odienné', 'Duékoué', 'Danané', 'Tingréla', 'Guiglo', 'Boundiali',
            'Agnibilékrou', 'Daoukro', 'Vavoua', 'Zouénoula', 'Tiassalé',
            'Toumodi', 'Akoupé', 'Lakota', 'Bongouanou', 'Mankono',
            'Bouna', 'Tanda', 'Arrah', 'Sassandra', 'Tiebissou', 'Mbatto',
            'Niakaramandougou', 'Guitry', 'Dianra', 'Koun-Fao', 'Gohitafla',
            'Jacqueville', 'Toumoukoro', 'Tabou', 'Bocanda', 'Bloléquin',
            'Grand-Lahou', 'Béoumi', 'Ouangolodougou', 'Touba', 'Téhini', 'Tienko',
            'Bako', 'Minignan', 'Kani', 'Sipilou', 'Tafiré', 'Niellé', 'Diawala',
            'Mbengué', 'Dikodougou', 'Guiembé', 'Kouto', 'Gbon', 'Kolia', 'Kasséré'
        ];

        $io->info('Importation des localités...');
        
        $localiteEntities = [];
        
        // Remove old localites if we want (optional), but let's just create new ones or fetch existing
        $repo = $this->entityManager->getRepository(Localite::class);
        
        foreach ($localites as $locName) {
            $localite = $repo->findOneBy(['libelle' => $locName]);
            if (!$localite) {
                $localite = new Localite();
                $localite->setLibelle($locName);
                $this->entityManager->persist($localite);
            }
            $localiteEntities[] = $localite;
        }
        $this->entityManager->flush();
        $io->success(count($localites) . ' localités vérifiées/créées.');

        $io->info('Création de Panneaux avec de vraies images...');

        // Let's create 10 Panneaux as an example
        for ($i = 1; $i <= 10; $i++) {
            $panneau = new Panneau();
            $panneau->setCode('PAN-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT));
            $panneau->setGpsLat('5.3' . rand(100, 999)); // Around Abidjan approx
            $panneau->setGpsLong('-4.0' . rand(100, 999));
            $panneau->setLocalite($localiteEntities[array_rand($localiteEntities)]);
            $panneau->setLocalisation('Avenue ' . rand(1, 20) . ', Rue ' . rand(1, 100));
            
            $face1 = new Face();
            $face1->setCode('FACE-' . $panneau->getCode() . '-1');
            $face1->setNumFace('1');
            $face1->setPrix((string)(rand(100, 500) * 1000));
            $face1->setEtat(Face::ETAT['Libre']);
            $face1->setPanneau($panneau);
            
            // Generate a real image file for face1
            $fichier1 = $this->createImageFichier('panneaux_faces', str_replace('-', '_', strtolower($face1->getCode())) . '_img');
            if ($fichier1) {
                $face1->setImagePrincipale($fichier1);
                $this->entityManager->persist($fichier1);
            }

            // Create face 2
            $face2 = new Face();
            $face2->setCode('FACE-' . $panneau->getCode() . '-2');
            $face2->setNumFace('2');
            $face2->setPrix((string)(rand(100, 500) * 1000));
            $face2->setEtat(Face::ETAT['Reserve']);
            $face2->setPanneau($panneau);
            
            // Generate a real image file for face2
            $fichier2 = $this->createImageFichier('panneaux_faces', str_replace('-', '_', strtolower($face2->getCode())) . '_img');
            if ($fichier2) {
                $face2->setImagePrincipale($fichier2);
                $this->entityManager->persist($fichier2);
            }
            
            $this->entityManager->persist($face1);
            $this->entityManager->persist($face2);
            $this->entityManager->persist($panneau);
            
            $io->text("Créé le panneau : " . $panneau->getCode());
        }

        $this->entityManager->flush();

        $io->success('Importation terminée avec succès ! Vous pouvez retrouver les images dans le dossier public/uploads/panneaux_faces.');

        return Command::SUCCESS;
    }

    private function createImageFichier(string $path, string $alt): ?Fichier
    {
        $uploadDir = $this->projectDir . '/public/uploads/' . $path;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // We download a random image
        // To avoid timeouts, we do this simply using file_get_contents and a timeout context
        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $imageData = @file_get_contents('https://picsum.photos/800/600', false, $ctx);
        
        if (!$imageData) {
            return null; // Silent fail if no internet
        }

        $fileName = uniqid() . '.jpg';
        $fullPath = $uploadDir . '/' . $fileName;
        file_put_contents($fullPath, $imageData);

        $fichier = new Fichier();
        $fichier->setPath($path);
        $fichier->setAlt($alt . '.jpg');
        $fichier->setUrl('jpg');
        $fichier->setSize((int)filesize($fullPath));
        
        $finalPath = $uploadDir . '/' . $fichier->getAlt();
        rename($fullPath, $finalPath);

        return $fichier;
    }
}
