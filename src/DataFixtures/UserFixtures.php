<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\User;
use App\Repository\CiviliteRepository;
use App\Repository\FonctionRepository;
use App\Repository\GenreRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;;

class UserFixtures extends Fixture 
{
    public const DEFAULT_USER_REFERENCE = 'default-user';

    private UserPasswordHasherInterface $hasher;
    private $genreRepository;
    private $civiliteRepository;
    private $fonctionRepository;

    public function __construct(UserPasswordHasherInterface $hasher,GenreRepository $genreRepository,CiviliteRepository $civiliteRepository,FonctionRepository $fonctionRepository)
    {
        $this->hasher = $hasher;
        $this->genreRepository = $genreRepository;
        $this->civiliteRepository = $civiliteRepository;
        $this->fonctionRepository = $fonctionRepository;
    }
   


    public function load(ObjectManager $manager): void
    {
        $admin = new Admin();
        $admin->setNom("Admin");
        $admin->setPrenoms("Admin");
        $admin->setContact("000000000");
        $admin->setFonction($this->fonctionRepository->find(1));
        $admin->setCivilite($this->civiliteRepository->find(1));
        $admin->setGenre($this->genreRepository->find(1));
        $admin->setCreatedAtValue(new \DateTime());
        $admin->setUpdatedAt(new \DateTime());

        $manager->persist($admin);

        $manager->flush();



        $utilisateur = new User();

        $utilisateur->setPersonne($admin);
        $utilisateur->setEmail('admin@gmail.com');
        $utilisateur->setPassword($this->hasher->hashPassword($utilisateur, 'admin_ufr'));
        
        // $product = new Product();
        // $manager->persist($product);
        $manager->persist($utilisateur);

        $manager->flush();

        $this->addReference(self::DEFAULT_USER_REFERENCE, $utilisateur);
    }


   
}
