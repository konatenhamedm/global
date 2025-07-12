<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: PersonneRepository::class)]
#[InheritanceType("JOINED")]
#[DiscriminatorColumn(name: "discr", type: "string", length: 18)]
#[DiscriminatorMap([
    'personne' => Personne::class,
    'admin' => Admin::class,
    'client' => Client::class
])]
class Personne
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_commande"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_commande"])]
    private ?string $contact = null;

    #[ORM\OneToOne(mappedBy: 'personne', cascade: ['persist', 'remove'])]
    private ?User $user = null;


    public function __construct()
    {
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        // set the owning side of the relation if necessary
        if ($user->getPersonne() !== $this) {
            $user->setPersonne($this);
        }

        $this->user = $user;

        return $this;
    }

    
}
