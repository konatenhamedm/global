<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
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
    #[Group(["group1","group_commande"])]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Client $client = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?\DateTime $dateCommande = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?string $montant = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $etat = null;

    /**
     * @var Collection<int, Ligne>
     */
    #[ORM\OneToMany(targetEntity: Ligne::class, mappedBy: 'commande')]
    #[Group(["group1","group_commande"])]
    private Collection $lignes;

    /**
     * @var Collection<int, Validation>
     */
    #[ORM\OneToMany(targetEntity: Validation::class, mappedBy: 'commande')]
    #[Group(["group1","group_commande"])]
    private Collection $validations;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?string $montantImpression = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?string $montantPose = null;


    /**
     * @var Collection<int, AvecImpression>
     */
    #[ORM\OneToMany(targetEntity: AvecImpression::class, mappedBy: 'commande')]
    private Collection $avecImpressions;

    /**
     * @var Collection<int, SansImpression>
     */
    #[ORM\OneToMany(targetEntity: SansImpression::class, mappedBy: 'commande')]
    private Collection $sansImpressions;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?string $montantProvisoire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?string $montantLocation = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1",'group_commande'])]
    private ?Fichier $fichierContrat = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?string $impressionVisuelle = null;


    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->dateCommande = new \DateTime();
        $this->validations = new ArrayCollection();
        $this->avecImpressions = new ArrayCollection();
        $this->sansImpressions = new ArrayCollection();
        $this->etat = "devis_attente";
        $this->dateCommande = new \DateTime();
        
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getDateCommande(): ?\DateTime
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTime $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

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
            $ligne->setCommande($this);
        }

        return $this;
    }

    public function removeLigne(Ligne $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            // set the owning side to null (unless already changed)
            if ($ligne->getCommande() === $this) {
                $ligne->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Validation>
     */
    public function getValidations(): Collection
    {
        return $this->validations;
    }

    public function addValidation(Validation $validation): static
    {
        if (!$this->validations->contains($validation)) {
            $this->validations->add($validation);
            $validation->setCommande($this);
        }

        return $this;
    }

    public function removeValidation(Validation $validation): static
    {
        if ($this->validations->removeElement($validation)) {
            // set the owning side to null (unless already changed)
            if ($validation->getCommande() === $this) {
                $validation->setCommande(null);
            }
        }

        return $this;
    }

    public function getMontantImpression(): ?string
    {
        return $this->montantImpression;
    }

    public function setMontantImpression(?string $montantImpression): static
    {
        $this->montantImpression = $montantImpression;

        return $this;
    }

    public function getMontantPose(): ?string
    {
        return $this->montantPose;
    }

    public function setMontantPose(?string $montantPose): static
    {
        $this->montantPose = $montantPose;

        return $this;
    }

   
    /**
     * @return Collection<int, AvecImpression>
     */
    public function getAvecImpressions(): Collection
    {
        return $this->avecImpressions;
    }

    public function addAvecImpression(AvecImpression $avecImpression): static
    {
        if (!$this->avecImpressions->contains($avecImpression)) {
            $this->avecImpressions->add($avecImpression);
            $avecImpression->setCommande($this);
        }

        return $this;
    }

    public function removeAvecImpression(AvecImpression $avecImpression): static
    {
        if ($this->avecImpressions->removeElement($avecImpression)) {
            // set the owning side to null (unless already changed)
            if ($avecImpression->getCommande() === $this) {
                $avecImpression->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SansImpression>
     */
    public function getSansImpressions(): Collection
    {
        return $this->sansImpressions;
    }

    public function addSansImpression(SansImpression $sansImpression): static
    {
        if (!$this->sansImpressions->contains($sansImpression)) {
            $this->sansImpressions->add($sansImpression);
            $sansImpression->setCommande($this);
        }

        return $this;
    }

    public function removeSansImpression(SansImpression $sansImpression): static
    {
        if ($this->sansImpressions->removeElement($sansImpression)) {
            // set the owning side to null (unless already changed)
            if ($sansImpression->getCommande() === $this) {
                $sansImpression->setCommande(null);
            }
        }

        return $this;
    }

    public function getMontantProvisoire(): ?string
    {
        return $this->montantProvisoire;
    }

    public function setMontantProvisoire(string $montantProvisoire): static
    {
        $this->montantProvisoire = $montantProvisoire;

        return $this;
    }

    public function getMontantLocation(): ?string
    {
        return $this->montantLocation;
    }

    public function setMontantLocation(?string $montantLocation): static
    {
        $this->montantLocation = $montantLocation;

        return $this;
    }

    public function getFichierContrat(): ?Fichier
    {
        return $this->fichierContrat;
    }

    public function setFichierContrat(Fichier $fichierContrat): static
    {
        $this->fichierContrat = $fichierContrat;

        return $this;
    }

    public function getImpressionVisuelle(): ?string
    {
        return $this->impressionVisuelle;
    }

    public function setImpressionVisuelle(?string $impressionVisuelle): static
    {
        $this->impressionVisuelle = $impressionVisuelle;

        return $this;
    }

    

  
}
