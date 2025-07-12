<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client extends Personne
{
    

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $prenoms = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $email = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $registreCommerce = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $denomination = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $compteContribuable = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $adresse = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $telComptabilite = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $emailComptabilite = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $nomStructureFacture = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Group(["group1"])]
    private ?string $localisation = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    #[Group(["group1"])]
    private ?TypeClient $typeClient = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'client')]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

   
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRegistreCommerce(): ?string
    {
        return $this->registreCommerce;
    }

    public function setRegistreCommerce(string $registreCommerce): static
    {
        $this->registreCommerce = $registreCommerce;

        return $this;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(string $denomination): static
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getCompteContribuable(): ?string
    {
        return $this->compteContribuable;
    }

    public function setCompteContribuable(string $compteContribuable): static
    {
        $this->compteContribuable = $compteContribuable;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelComptabilite(): ?string
    {
        return $this->telComptabilite;
    }

    public function setTelComptabilite(string $telComptabilite): static
    {
        $this->telComptabilite = $telComptabilite;

        return $this;
    }

    public function getEmailComptabilite(): ?string
    {
        return $this->emailComptabilite;
    }

    public function setEmailComptabilite(string $emailComptabilite): static
    {
        $this->emailComptabilite = $emailComptabilite;

        return $this;
    }

    public function getNomStructureFacture(): ?string
    {
        return $this->nomStructureFacture;
    }

    public function setNomStructureFacture(string $nomStructureFacture): static
    {
        $this->nomStructureFacture = $nomStructureFacture;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getTypeClient(): ?TypeClient
    {
        return $this->typeClient;
    }

    public function setTypeClient(?TypeClient $typeClient): static
    {
        $this->typeClient = $typeClient;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setClient($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getClient() === $this) {
                $commande->setClient(null);
            }
        }

        return $this;
    }
}
