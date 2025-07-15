<?php

namespace App\Entity;

use App\Repository\SansImpressionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: SansImpressionRepository::class)]
class SansImpression
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_commande"])]
    private ?int $id = null;

    // ETAPE ENVOI VISUEL BACHE

    #[ORM\Column(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?\DateTime $dateEnvoiBache = null;


    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?Fichier $visualBache = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?string $commentaireEnvoiBache = null;

    // ETAPE PROGRAMMATION POSE

    #[ORM\Column(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?\DateTime $dateProgrammationPose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?string $commentaireProgrammationpose = null;

    // ETAPE RAPPORT DEPOSE



    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?Fichier $rapportPoseImage = null;

    #[ORM\Column(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?\DateTime $dateRapportPose = null;

    

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?string $commentRapportPose = null;

    // ETAPE RAPPORT DEPOSE

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?Fichier $rapportDepose = null;

    #[ORM\Column(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?\DateTime $dateRapportDepose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?string $commentaireRapportDepose = null;

    // ETAPE FINALISATION   bbb

    #[ORM\Column(nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?\DateTime $dateFinalisation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["fichier", "group1","group_commande"])]
    private ?string $commentaireFinalisation = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $etape = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'sansImpression')]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateEnvoiBache(): ?\DateTime
    {
        return $this->dateEnvoiBache;
    }

    public function setDateEnvoiBache(?\DateTime $dateEnvoiBache): static
    {
        $this->dateEnvoiBache = $dateEnvoiBache;

        return $this;
    }

    public function getVisualBache(): ?Fichier
    {
        return $this->visualBache;
    }

    public function setVisualBache(?Fichier $visualBache): static
    {
        $this->visualBache = $visualBache;

        return $this;
    }

    public function getCommentaireEnvoiBache(): ?string
    {
        return $this->commentaireEnvoiBache;
    }

    public function setCommentaireEnvoiBache(?string $commentaireEnvoiBache): static
    {
        $this->commentaireEnvoiBache = $commentaireEnvoiBache;

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

    public function getCommentaireProgrammationpose(): ?string
    {
        return $this->commentaireProgrammationpose;
    }

    public function setCommentaireProgrammationpose(?string $commentaireProgrammationpose): static
    {
        $this->commentaireProgrammationpose = $commentaireProgrammationpose;

        return $this;
    }

    public function getRapportPoseImage(): ?Fichier
    {
        return $this->rapportPoseImage;
    }

    public function setRapportPoseImage(?Fichier $rapportPoseImage): static
    {
        $this->rapportPoseImage = $rapportPoseImage;

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

    public function getCommentRapportPose(): ?string
    {
        return $this->commentRapportPose;
    }

    public function setCommentRapportPose(?string $commentRapportPose): static
    {
        $this->commentRapportPose = $commentRapportPose;

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

    public function getCommentaireRapportDepose(): ?string
    {
        return $this->commentaireRapportDepose;
    }

    public function setCommentaireRapportDepose(string $commentaireRapportDepose): static
    {
        $this->commentaireRapportDepose = $commentaireRapportDepose;

        return $this;
    }

    public function getDateFinalisation(): ?\DateTime
    {
        return $this->dateFinalisation;
    }

    public function setDateFinalisation(?\DateTime $dateFinalisation): static
    {
        $this->dateFinalisation = $dateFinalisation;

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

    public function setEtape(string $etape): static
    {
        $this->etape = $etape;

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
            $commande->setSansImpression($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getSansImpression() === $this) {
                $commande->setSansImpression(null);
            }
        }

        return $this;
    }

   
}
