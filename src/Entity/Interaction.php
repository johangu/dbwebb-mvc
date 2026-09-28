<?php

namespace App\Entity;

use App\Repository\InteractionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InteractionRepository::class)]
class Interaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $target = null;

    #[ORM\ManyToOne]
    private ?Item $usedItem = null;

    #[ORM\ManyToOne]
    private ?Interaction $requiredInteraction = null;

    #[ORM\ManyToOne]
    private ?Item $revealsItem = null;

    #[ORM\ManyToOne]
    private ?Passage $unlocksPassage = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $consumesUsedItem = null;

    #[ORM\Column]
    private ?bool $wins = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

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

    public function getTarget(): ?Item
    {
        return $this->target;
    }

    public function setTarget(?Item $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getUsedItem(): ?Item
    {
        return $this->usedItem;
    }

    public function setUsedItem(?Item $usedItem): static
    {
        $this->usedItem = $usedItem;

        return $this;
    }

    public function getRequiredInteraction(): ?self
    {
        return $this->requiredInteraction;
    }

    public function setRequiredInteraction(?self $requiredInteraction): static
    {
        $this->requiredInteraction = $requiredInteraction;

        return $this;
    }

    public function getRevealsItem(): ?Item
    {
        return $this->revealsItem;
    }

    public function setRevealsItem(?Item $revealsItem): static
    {
        $this->revealsItem = $revealsItem;

        return $this;
    }

    public function getUnlocksPassage(): ?Passage
    {
        return $this->unlocksPassage;
    }

    public function setUnlocksPassage(?Passage $unlocksPassage): static
    {
        $this->unlocksPassage = $unlocksPassage;

        return $this;
    }

    public function isConsumesUsedItem(): ?bool
    {
        return $this->consumesUsedItem;
    }

    public function setConsumesUsedItem(bool $consumesUsedItem): static
    {
        $this->consumesUsedItem = $consumesUsedItem;

        return $this;
    }

    public function isWins(): ?bool
    {
        return $this->wins;
    }

    public function setWins(bool $wins): static
    {
        $this->wins = $wins;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
