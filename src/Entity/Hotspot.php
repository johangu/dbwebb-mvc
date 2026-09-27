<?php

namespace App\Entity;

use App\Repository\HotspotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HotspotRepository::class)]
class Hotspot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne]
    private ?Item $item = null;

    #[ORM\ManyToOne]
    private ?Passage $passage = null;

    #[ORM\Column]
    private ?float $leftPercent = null;

    #[ORM\Column]
    private ?float $topPercent = null;

    #[ORM\Column]
    private ?float $widthPercent = null;

    #[ORM\Column]
    private ?float $heightPercent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getPassage(): ?Passage
    {
        return $this->passage;
    }

    public function setPassage(?Passage $passage): static
    {
        $this->passage = $passage;

        return $this;
    }

    public function getLeftPercent(): ?float
    {
        return $this->leftPercent;
    }

    public function setLeftPercent(float $leftPercent): static
    {
        $this->leftPercent = $leftPercent;

        return $this;
    }

    public function getTopPercent(): ?float
    {
        return $this->topPercent;
    }

    public function setTopPercent(float $topPercent): static
    {
        $this->topPercent = $topPercent;

        return $this;
    }

    public function getWidthPercent(): ?float
    {
        return $this->widthPercent;
    }

    public function setWidthPercent(float $widthPercent): static
    {
        $this->widthPercent = $widthPercent;

        return $this;
    }

    public function getHeightPercent(): ?float
    {
        return $this->heightPercent;
    }

    public function setHeightPercent(float $heightPercent): static
    {
        $this->heightPercent = $heightPercent;

        return $this;
    }
}
