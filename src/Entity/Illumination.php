<?php

namespace App\Entity;

use App\Repository\IlluminationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: IlluminationRepository::class)]
class Illumination
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Panneau>
     */
    #[ORM\OneToMany(targetEntity: Panneau::class, mappedBy: 'illumination')]
    private Collection $panneaus;

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
            $panneau->setIllumination($this);
        }

        return $this;
    }

    public function removePanneau(Panneau $panneau): static
    {
        if ($this->panneaus->removeElement($panneau)) {
            // set the owning side to null (unless already changed)
            if ($panneau->getIllumination() === $this) {
                $panneau->setIllumination(null);
            }
        }

        return $this;
    }
}
