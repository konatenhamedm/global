<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{ use TraitEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $code = null;

    /**
     * @var Collection<int, Panneau>
     */
    #[ORM\OneToMany(targetEntity: Panneau::class, mappedBy: 'zone')]
    private Collection $panneaus;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $centre = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $zoom = null;

    public function __construct()
    {
        $this->panneaus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
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

    /**
     * @return Collection<int, Panneau>
     */
    public function getPanneaus(): Collection
    {
        return $this->panneaus;
    }

    public function addPanneau(Panneau $panneau): static
    {
        if (!$this->panneaus->contains($panneau)) {
            $this->panneaus->add($panneau);
            $panneau->setZone($this);
        }

        return $this;
    }

    public function removePanneau(Panneau $panneau): static
    {
        if ($this->panneaus->removeElement($panneau)) {
            // set the owning side to null (unless already changed)
            if ($panneau->getZone() === $this) {
                $panneau->setZone(null);
            }
        }

        return $this;
    }

    public function getCentre(): ?string
    {
        return $this->centre;
    }

    public function setCentre(string $centre): static
    {
        $this->centre = $centre;

        return $this;
    }

    public function getZoom(): ?string
    {
        return $this->zoom;
    }

    public function setZoom(string $zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }
}
