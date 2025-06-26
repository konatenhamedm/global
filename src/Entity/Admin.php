<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: '`admin`')]
class Admin extends Personne
{
    

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $prenoms = null;

    #[ORM\ManyToOne(inversedBy: 'admins')]
    #[Group(["group1"])]
    private ?Genre $genre = null;

    #[ORM\ManyToOne(inversedBy: 'admins')]
    #[Group(["group1"])]
    private ?Civilite $civilite = null;

    #[ORM\ManyToOne(inversedBy: 'admins')]
    #[ORM\JoinColumn(name: "fonction_id", referencedColumnName: "id", nullable: true)]
    #[Group(["group1"])]
    private ?Fonction $fonction = null;

    

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return $this->prenoms;
    }

    public function setPrenoms(string $prenoms): static
    {
        $this->prenoms = $prenoms;

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getFonction(): ?Fonction
    {
        return $this->fonction;
    }

    public function setFonction(?Fonction $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }
}