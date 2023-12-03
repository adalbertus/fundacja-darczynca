<?php

namespace App\Entity;

use App\Repository\DonorSearchPatternRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: DonorSearchPatternRepository::class)]
class DonorSearchPattern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Fraza wyszukiwania nie może być pusta.')]
    private ?string $search_pattern = null;

    #[ORM\ManyToOne(inversedBy: 'donorSearchPatterns')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?Donor $donor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSearchPattern(): ?string
    {
        return $this->search_pattern;
    }

    public function setSearchPattern(string $search_pattern): static
    {
        $this->search_pattern = $search_pattern;

        return $this;
    }

    public function getDonor(): ?Donor
    {
        return $this->donor;
    }

    public function setDonor(?Donor $donor): static
    {
        $this->donor = $donor;

        return $this;
    }

    public function __toString(): string
    {
        $searchPattern = $this->getSearchPattern();
        $donor = "donor_id=" . $this->getDonor()->getId();
        return "{$searchPattern} [{$donor}]";
    }
}
