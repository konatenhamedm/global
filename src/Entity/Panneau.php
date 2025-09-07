<?php

namespace App\Entity;

use App\Repository\PanneauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: PanneauRepository::class)]
class Panneau
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_commande"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $gpsLong = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $gpsLat = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Type $type = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?SousType $sousType = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Taille $taille = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Illumination $illumination = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1","group_commande"])]
    private ?Localite $localite = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Superficie $superficie = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Orientation $orientation = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Substrat $substrat = null;

    /**
     * @var Collection<int, Face>
     */
    #[ORM\OneToMany(targetEntity: Face::class, mappedBy: 'panneau', orphanRemoval: true, cascade: ['persist'])]
    #[Group(["group1"])]
    private Collection $faces;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1","group_commande"])]
    private ?Specification $specification = null;

  

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group1"])]
    private ?string $localisation = null;

    #[ORM\ManyToOne(inversedBy: 'panneaus')]
    #[Group(["group1"])]
    private ?Zone $zone = null;

    public function __construct()
    {
        $this->faces = new ArrayCollection();
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

    public function getGpsLong(): ?string
    {
        return $this->gpsLong;
    }

    public function setGpsLong(string $gpsLong): static
    {
        $this->gpsLong = $gpsLong;

        return $this;
    }

    public function getGpsLat(): ?string
    {
        return $this->gpsLat;
    }

    public function setGpsLat(string $gpsLat): static
    {
        $this->gpsLat = $gpsLat;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSousType(): ?SousType
    {
        return $this->sousType;
    }

    public function setSousType(?SousType $sousType): static
    {
        $this->sousType = $sousType;

        return $this;
    }

    public function getTaille(): ?Taille
    {
        return $this->taille;
    }

    public function setTaille(?Taille $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getIllumination(): ?Illumination
    {
        return $this->illumination;
    }

    public function setIllumination(?Illumination $illumination): static
    {
        $this->illumination = $illumination;

        return $this;
    }

    public function getLocalite(): ?Localite
    {
        return $this->localite;
    }

    public function setLocalite(?Localite $localite): static
    {
        $this->localite = $localite;

        return $this;
    }

    public function getSuperficie(): ?Superficie
    {
        return $this->superficie;
    }

    public function setSuperficie(?Superficie $superficie): static
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getOrientation(): ?Orientation
    {
        return $this->orientation;
    }

    public function setOrientation(?Orientation $orientation): static
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getSubstrat(): ?Substrat
    {
        return $this->substrat;
    }

    public function setSubstrat(?Substrat $substrat): static
    {
        $this->substrat = $substrat;

        return $this;
    }

    /**
     * @return Collection<int, Face>
     */
    public function getFaces(): Collection
    {
        return $this->faces;
    }

    public function addFace(Face $face): static
    {
        if (!$this->faces->contains($face)) {
            $this->faces->add($face);
            $face->setPanneau($this);
        }

        return $this;
    }

    public function removeFace(Face $face): static
    {
        if ($this->faces->removeElement($face)) {
            // set the owning side to null (unless already changed)
            if ($face->getPanneau() === $this) {
                $face->setPanneau(null);
            }
        }

        return $this;
    }

    public function getSpecification(): ?Specification
    {
        return $this->specification;
    }

    public function setSpecification(?Specification $specification): static
    {
        $this->specification = $specification;

        return $this;
    }


    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }
}
