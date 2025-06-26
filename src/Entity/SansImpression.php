<?php

namespace App\Entity;

use App\Repository\SansImpressionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SansImpressionRepository::class)]
class SansImpression
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ETAPE ENVOI VISUEL BACHE

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateEnvoiBache = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $visualBache = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireEnvoiBache = null;

    // ETAPE PROGRAMMATION POSE

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateProgrammationPose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireProgrammationpose = null;

    // ETAPE RAPPORT DEPOSE

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rapportPoseImage = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateRapportPose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentRapportPose = null;

    // ETAPE RAPPORT DEPOSE

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rapportDepose = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateRapportDepose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireRapportDepose = null;

    // ETAPE FINALISATION   bbb

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateFinalisation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireFinalisation = null;

    #[ORM\Column(length: 255)]
    private ?string $etape = null;

    #[ORM\ManyToOne(inversedBy: 'sansImpressions')]
    private ?Commande $commande = null;

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

    public function getVisualBache(): ?string
    {
        return $this->visualBache;
    }

    public function setVisualBache(?string $visualBache): static
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

    public function getRapportPoseImage(): ?string
    {
        return $this->rapportPoseImage;
    }

    public function setRapportPoseImage(?string $rapportPoseImage): static
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

    public function getRapportDepose(): ?string
    {
        return $this->rapportDepose;
    }

    public function setRapportDepose(?string $rapportDepose): static
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

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }
}
