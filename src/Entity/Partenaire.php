<?php

namespace App\Entity;

use App\Repository\PartenaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
#[ORM\Table(name: 'partenaire')]
#[ORM\HasLifecycleCallbacks]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1", "partenaire"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du partenaire est obligatoire.")]
    #[Group(["group1", "partenaire"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1", "partenaire"])]
    private ?string $siteWeb = null;

    #[ORM\Column(options: ["default" => 0])]
    #[Group(["group1", "partenaire"])]
    private int $ordre = 0;

    #[ORM\Column(options: ["default" => true])]
    #[Group(["group1", "partenaire"])]
    private bool $actif = true;

    #[ORM\ManyToOne(targetEntity: Fichier::class, cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Fichier $logo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Group(["group1", "partenaire"])]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Group(["group1", "partenaire"])]
    private ?\DateTimeInterface $dateMaj = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $userUpdate = null;

    public function __construct()
    {
        $this->ordre = 0;
        $this->actif = true;
        $this->dateCreation = new \DateTime();
        $this->dateMaj = new \DateTime();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }
        $this->dateMaj = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateMaj = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = trim($nom);

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb !== null ? trim($siteWeb) : null;
        if ($this->siteWeb === '') {
            $this->siteWeb = null;
        }

        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    #[Group(["group1", "partenaire"])]
    public function getLogo(): ?array
    {
        if ($this->logo === null) {
            return null;
        }

        $alt = $this->logo->getAlt();
        $path = $this->logo->getPath() ?: 'partenaires';
        $baseUrl = 'http://global.ticleaders.net';

        return [
            'alt' => $alt,
            'path' => $path,
            'url' => rtrim($baseUrl, '/') . '/uploads/' . trim($path, '/') . '/' . $alt,
        ];
    }

    public function getLogoFichier(): ?Fichier
    {
        return $this->logo;
    }

    public function setLogo(?Fichier $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateMaj(): ?\DateTimeInterface
    {
        return $this->dateMaj;
    }

    public function setDateMaj(\DateTimeInterface $dateMaj): static
    {
        $this->dateMaj = $dateMaj;

        return $this;
    }

    public function getUserUpdate(): ?User
    {
        return $this->userUpdate;
    }

    public function setUserUpdate(?User $userUpdate): static
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }
}
