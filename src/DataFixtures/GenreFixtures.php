<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class GenreFixtures extends Fixture
{
    public const DEFAULT_GENRE_REFERENCE = 'default-genre';
    public function load(ObjectManager $manager): void
    {
        $civilite = new Genre();
        $civilite->setLibelle('Homme');
        $civilite->setCode('H');
        // $product = new Product();
        $manager->persist($civilite);

        $manager->flush();

        $this->addReference(self::DEFAULT_GENRE_REFERENCE, $civilite);
    }
}
