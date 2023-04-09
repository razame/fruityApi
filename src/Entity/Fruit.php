<?php

namespace App\Entity;

use JetBrains\PhpStorm\Pure;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FruitRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: FruitRepository::class)]
class Fruit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genus = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $family = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fruit_order = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nutritions = null;

    #[ORM\ManyToMany(targetEntity:"App\Entity\User", mappedBy:"fruits")]
    #[ORM\JoinTable(name:"favorites", joinColumns: 'user_id', inverseJoinColumns: 'id')]
    private $users;

    #[Pure] public function __construct() {
        $this->users = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGenus(): ?string
    {
        return $this->genus;
    }

    public function setGenus(?string $genus): self
    {
        $this->genus = $genus;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(?string $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getFruitOrder(): ?string
    {
        return $this->fruit_order;
    }

    public function setFruitOrder(?string $fruit_order): self
    {
        $this->fruit_order = $fruit_order;

        return $this;
    }

    public function getNutritions(): string
    {
        return $this->nutritions;
    }

    public function setNutritions(?string $nutritions): self
    {
        $this->nutritions = $nutritions;

        return $this;
    }

}
