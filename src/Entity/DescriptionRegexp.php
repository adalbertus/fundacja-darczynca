<?php

namespace App\Entity;

use App\Constants\AccountKeys;
use App\Constants\CategoryKeys;
use App\Repository\DescriptionRegexpRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DescriptionRegexpRepository::class)]
class DescriptionRegexp extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private ?string $expression = null;

    #[ORM\Column(length: 32, options: ["default" => CategoryKeys::BRAK])]
    private ?string $category = null;

    #[ORM\Column(length: 32, options: ["default" => CategoryKeys::BRAK])]
    private ?string $sub_category = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function setExpression(string $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSubCategory(): ?string
    {
        return $this->sub_category;
    }

    public function setSubCategory(string $sub_category): self
    {
        $this->sub_category = $sub_category;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}