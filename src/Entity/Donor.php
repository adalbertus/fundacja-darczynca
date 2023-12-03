<?php

namespace App\Entity;

use App\Constants\ErrorCodes;
use App\Entity\BaseEntity;
use App\Repository\DonorRepository;
use App\Service\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity("name", message: 'Nazwa darczyńcy ({{ value }}) już istnieje w systemie.')]
#[Assert\Cascade]
#[ORM\Entity(repositoryClass: DonorRepository::class)]
class Donor extends BaseEntity implements RegexpPatternInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'donors')]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_auto = null;

    #[Assert\NotBlank(message: 'Nazwa darczyńcy nie może być pusta')]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToMany(mappedBy: 'donor', targetEntity: BankHistory::class)]
    private Collection $bankHistoryTransactions;

    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: ErrorCodes::PRZYNAJMNIEJ_JEDNA_FRAZA_TEXT)]
    #[ORM\OneToMany(mappedBy: 'donor', targetEntity: DonorSearchPattern::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $donorSearchPatterns;

    public function __construct()
    {
        $this->isAuto = true;
        $this->bankHistoryTransactions = new ArrayCollection();
        $this->donorSearchPatterns = new ArrayCollection();
    }

    public function isNew(): bool
    {
        return is_null($this->getId());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isAuto(): ?bool
    {
        return $this->is_auto;
    }

    public function setIsAuto(bool $is_auto): static
    {
        $this->is_auto = $is_auto;

        return $this;
    }

    public function getName(): ?string
    {
        if (is_null($this->name)) {
            return '';
        }
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection<int, BankHistory>
     */
    public function getBankHistoryTransactions(): Collection
    {
        return $this->bankHistoryTransactions;
    }

    public function addBankHistory(BankHistory $bankHistory): static
    {
        if (!$this->bankHistoryTransactions->contains($bankHistory)) {
            $this->bankHistoryTransactions->add($bankHistory);
            $bankHistory->setDonor($this);
        }

        return $this;
    }

    public function removeBankHistory(BankHistory $bankHistory): static
    {
        if ($this->bankHistoryTransactions->removeElement($bankHistory)) {
            // set the owning side to null (unless already changed)
            if ($bankHistory->getDonor() === $this) {
                $bankHistory->setDonor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DonorSearchPattern>
     */
    public function getDonorSearchPatterns(): Collection
    {
        return $this->donorSearchPatterns;
    }

    public function addDonorSearchPattern(DonorSearchPattern $donorSearchPattern): static
    {
        if (!$this->donorSearchPatterns->contains($donorSearchPattern)) {
            $this->donorSearchPatterns->add($donorSearchPattern);
            $donorSearchPattern->setDonor($this);
        }

        return $this;
    }

    public function removeDonorSearchPattern(DonorSearchPattern $donorSearchPattern): static
    {
        if ($this->donorSearchPatterns->removeElement($donorSearchPattern)) {
            // set the owning side to null (unless already changed)
            if ($donorSearchPattern->getDonor() === $this) {
                $donorSearchPattern->setDonor(null);
            }
        }

        return $this;
    }

    public function buildRegexpPattern(): string
    {
        $patterns = [];
        foreach ($this->getDonorSearchPatterns() as $donorSearchPattern) {
            $searchPattern = $donorSearchPattern->getSearchPattern();
            if (!StringHelper::isNullOrEmpty($searchPattern)) {
                $patterns[] = $searchPattern;
            }
        }
        return join('|', $patterns);
    }

    public function getDisplayName(): string
    {
        return $this->getName();

        // if (is_null($this->getUser())) {
        //     return $this->getName();
        // }
        // return $this->getUser()->getDisplayName();
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    /**
     * Zwrócenie adresu e-mail przypisanego użytkownika
     * @return string adres e-mail lub pusty string
     */
    public function getUserEmailOrEmpty(): string
    {
        if ($this->getUser() === null) {
            return '';
        }
        return $this->getUser()->getEmail();
    }
}
