<?php

namespace App\Entity;

use App\Repository\AvecImpressionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Doctrine\DBAL\Types\Types;


#[ORM\Entity(repositoryClass: AvecImpressionRepository::class)]
class AvecImpression
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group_commande"])]
    private ?int $id = null;


    //ETAPE 1 

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?Fichier $envoiVisuel = null;

    #[ORM\Column(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?\DateTime $dateEnvoiVisuel = null;

    //ETAPE 2


    #[ORM\Column(nullable: true)]
    #[Group(["group1","group_commande"])]
    private ?\DateTime $dateImpressionBat = null;

 

    //etape 3

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateValidationBat = null;

    
    //ETAPE 4

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateImpressionvisuelle = null;

   
    //ETAPE 5
    #[Group(["group_commande"])]
    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateProgrammationPose = null;


    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateDebutPose = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateFinPose = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateDebutAlerte = null;

    //ETAPE 6

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateRapportPose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentairePose = null;

   //ETAPE 7

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier",'group_commande'])]
    private ?Fichier $rapportDepose = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateRapportDepose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireRapportDepose = null;

//ETAPE 8

    #[ORM\Column(nullable: true)]
    #[Group(["group_commande"])]
    private ?\DateTime $dateFinalisation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireFinalisation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $etape = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireEnvoiVisuel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireImpressionBat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireValidationBat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireImpressionVisuelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_commande"])]
    private ?string $commentaireProgrammationPose = null;

   
    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_commande"])]
    private ?Fichier $imageImpressionVisuelle = null;

   

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_commande"])]
    private ?Fichier $rapportPose = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'avecImpression')]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }


  
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnvoiVisuel(): ?Fichier
    {
        return $this->envoiVisuel;
    }

    public function setEnvoiVisuel(?Fichier $envoiVisuel): static
    {
        $this->envoiVisuel = $envoiVisuel;

        return $this;
    }

    public function getDateEnvoiVisuel(): ?\DateTime
    {
        return $this->dateEnvoiVisuel;
    }

    public function setDateEnvoiVisuel(?\DateTime $dateEnvoiVisuel): static
    {
        $this->dateEnvoiVisuel = $dateEnvoiVisuel;

        return $this;
    }

   

    public function getDateImpressionBat(): ?\DateTime
    {
        return $this->dateImpressionBat;
    }

    public function setDateImpressionBat(?\DateTime $dateImpressionBat): static
    {
        $this->dateImpressionBat = $dateImpressionBat;

        return $this;
    }

    

    public function getDateValidationBat(): ?\DateTime
    {
        return $this->dateValidationBat;
    }

    public function setDateValidationBat(?\DateTime $dateValidationBat): static
    {
        $this->dateValidationBat = $dateValidationBat;

        return $this;
    }

    

    public function getDateImpressionvisuelle(): ?\DateTime
    {
        return $this->dateImpressionvisuelle;
    }

    public function setDateImpressionvisuelle(?\DateTime $dateImpressionvisuelle): static
    {
        $this->dateImpressionvisuelle = $dateImpressionvisuelle;

        return $this;
    }

    public function getDateProgrammationPose(): ?\DateTime
    {
        return $this->dateProgrammationPose;
    }

    public function setDateProgrammationPose(?\DateTime $dateProgrammationPose): static
    {
        $this->dateProgrammationPose = $dateProgrammationPose;

        return $this;
    }

   
    public function getDateRapportPose(): ?\DateTime
    {
        return $this->dateRapportPose;
    }

    public function setDateRapportPose(?\DateTime $dateRapportPose): static
    {
        $this->dateRapportPose = $dateRapportPose;

        return $this;
    }

    

    public function getRapportDepose(): ?Fichier
    {
        return $this->rapportDepose;
    }

    public function setRapportDepose(?Fichier $rapportDepose): static
    {
        $this->rapportDepose = $rapportDepose;

        return $this;
    }

    public function getDateRapportDepose(): ?\DateTime
    {
        return $this->dateRapportDepose;
    }

    public function setDateRapportDepose(?\DateTime $dateRapportDepose): static
    {
        $this->dateRapportDepose = $dateRapportDepose;

        return $this;
    }

   
    public function getDateFinalisation(): ?\DateTime
    {
        return $this->dateFinalisation;
    }

    public function setDateFinalisation(\DateTime $dateFinalisation): static
    {
        $this->dateFinalisation = $dateFinalisation;

        return $this;
    }

    public function getDateDebutPose(): ?\DateTime
    {
        return $this->dateDebutPose;
    }

    public function setDateDebutPose(?\DateTime $dateDebutPose): static
    {
        $this->dateDebutPose = $dateDebutPose;

        return $this;
    }

    public function getDateFinPose(): ?\DateTime
    {
        return $this->dateFinPose;
    }

    public function setDateFinPose(?\DateTime $dateFinPose): static
    {
        $this->dateFinPose = $dateFinPose;

        return $this;
    }

    public function getDateDebutAlerte(): ?\DateTime
    {
        return $this->dateDebutAlerte;
    }

    public function setDateDebutAlerte(?\DateTime $dateDebutAlerte): static
    {
        $this->dateDebutAlerte = $dateDebutAlerte;

        return $this;
    }

    public function getCommentairePose(): ?string
    {
        return $this->commentairePose;
    }

    public function setCommentairePose(?string $commentairePose): static
    {
        $this->commentairePose = $commentairePose;

        return $this;
    }

    public function getCommentaireRapportDepose(): ?string
    {
        return $this->commentaireRapportDepose;
    }

    public function setCommentaireRapportDepose(?string $commentaireRapportDepose): static
    {
        $this->commentaireRapportDepose = $commentaireRapportDepose;

        return $this;
    }

    public function getCommentaireFinalisation(): ?string
    {
        return $this->commentaireFinalisation;
    }

    public function setCommentaireFinalisation(?string $commentaireFinalisation): static
    {
        $this->commentaireFinalisation = $commentaireFinalisation;

        return $this;
    }

    public function getEtape(): ?string
    {
        return $this->etape;
    }

    public function setEtape(?string $etape): static
    {
        $this->etape = $etape;

        return $this;
    }

    public function getCommentaireEnvoiVisuel(): ?string
    {
        return $this->commentaireEnvoiVisuel;
    }

    public function setCommentaireEnvoiVisuel(?string $commentaireEnvoiVisuel): static
    {
        $this->commentaireEnvoiVisuel = $commentaireEnvoiVisuel;

        return $this;
    }

    public function getCommentaireImpressionBat(): ?string
    {
        return $this->commentaireImpressionBat;
    }

    public function setCommentaireImpressionBat(?string $commentaireImpressionBat): static
    {
        $this->commentaireImpressionBat = $commentaireImpressionBat;

        return $this;
    }

    public function getCommentaireValidationBat(): ?string
    {
        return $this->commentaireValidationBat;
    }

    public function setCommentaireValidationBat(?string $commentaireValidationBat): static
    {
        $this->commentaireValidationBat = $commentaireValidationBat;

        return $this;
    }

    public function getCommentaireImpressionVisuelle(): ?string
    {
        return $this->commentaireImpressionVisuelle;
    }

    public function setCommentaireImpressionVisuelle(?string $commentaireImpressionVisuelle): static
    {
        $this->commentaireImpressionVisuelle = $commentaireImpressionVisuelle;

        return $this;
    }

    public function getCommentaireProgrammationPose(): ?string
    {
        return $this->commentaireProgrammationPose;
    }

    public function setCommentaireProgrammationPose(?string $commentaireProgrammationPose): static
    {
        $this->commentaireProgrammationPose = $commentaireProgrammationPose;

        return $this;
    }

    
    public function getImageImpressionVisuelle(): ?Fichier
    {
        return $this->imageImpressionVisuelle;
    }

    public function setImageImpressionVisuelle(?Fichier $imageImpressionVisuelle): static
    {
        $this->imageImpressionVisuelle = $imageImpressionVisuelle;

        return $this;
    }

    public function getRapportPose(): ?Fichier
    {
        return $this->rapportPose;
    }

    public function setRapportPose(?Fichier $rapportPose): static
    {
        $this->rapportPose = $rapportPose;

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
            $commande->setAvecImpression($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getAvecImpression() === $this) {
                $commande->setAvecImpression(null);
            }
        }

        return $this;
    }
}
