<?php

namespace App\Entity;

use App\Repository\PassageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PassageRepository::class)]
class Passage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $fromRoom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $toRoom = null;

    #[ORM\Column(length: 255)]
    private ?string $direction = null;

    #[ORM\Column(length: 255)]
    private ?string $verb = null;

    #[ORM\Column]
    private ?bool $startsLocked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromRoom(): ?Room
    {
        return $this->fromRoom;
    }

    public function setFromRoom(?Room $fromRoom): static
    {
        $this->fromRoom = $fromRoom;

        return $this;
    }

    public function getToRoom(): ?Room
    {
        return $this->toRoom;
    }

    public function setToRoom(?Room $toRoom): static
    {
        $this->toRoom = $toRoom;

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function getVerb(): ?string
    {
        return $this->verb;
    }

    public function setVerb(string $verb): static
    {
        $this->verb = $verb;

        return $this;
    }

    public function isStartsLocked(): ?bool
    {
        return $this->startsLocked;
    }

    public function setStartsLocked(bool $startsLocked): static
    {
        $this->startsLocked = $startsLocked;

        return $this;
    }
}
