<?php

namespace App\Entity;

use App\Repository\FaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: FaceRepository::class)]
class Face
{
    use TraitEntity; 

    const etat = [
        'Libre' => "Libre",
        'Reserve' => "Reserve",
        'Encours' => "Encours",
    ];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_commande"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $numFace = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $prix = null;

   

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1"])]
    private ?Fichier $image = null;
    
    #[ORM\ManyToOne(inversedBy: 'faces')]
    #[Group(["group_commande"])]
    private ?Panneau $panneau = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $etat = null;

    /**
     * @var Collection<int, Ligne>
     */
    #[ORM\OneToMany(targetEntity: Ligne::class, mappedBy: 'face')]
    private Collection $lignes;

    #[ORM\Column(nullable: true)]
    #[Group(["group1"])]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group1"])]
    private ?\DateTime $dateFin = null;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->etat = "Libre";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getNumFace(): ?string
    {
        return $this->numFace;
    }

    public function setNumFace(string $numFace): static
    {
        $this->numFace = $numFace;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getImage(): ?Fichier
    {
        return $this->image;
    }

    public function setImage(?Fichier $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPanneau(): ?Panneau
    {
        return $this->panneau;
    }

    public function setPanneau(?Panneau $panneau): static
    {
        $this->panneau = $panneau;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Ligne>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(Ligne $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setFace($this);
        }

        return $this;
    }

    public function removeLigne(Ligne $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            // set the owning side to null (unless already changed)
            if ($ligne->getFace() === $this) {
                $ligne->setFace(null);
            }
        }

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }
}
