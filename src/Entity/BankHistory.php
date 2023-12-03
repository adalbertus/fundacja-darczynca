<?php

namespace App\Entity;

use App\Constants\ErrorCodes;
use App\Repository\BankHistoryRepository;
use App\Service\DateTimeHelper;
use App\Service\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Constants\CategoryKeys;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BankHistoryRepository::class)]
#[ORM\Index(name: 'category_idx', columns: ['category'])]
#[ORM\Index(name: 'subcategory_idx', columns: ['sub_category'])]
#[ORM\Index(name: 'date_idx', columns: ['date'])]
#[ORM\Index(name: 'description', columns: ['description'], flags: ['fulltext'])]
class BankHistory extends BaseEntity
{
    private bool $isValid;
    private ConstraintViolationList $violationList;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $value = null;

    #[ORM\Column(length: 32, nullable: false, options: ["default" => CategoryKeys::BRAK])]
    private ?string $category = null;

    #[ORM\Column(length: 32, nullable: false, options: ["default" => CategoryKeys::BRAK])]
    private ?string $sub_category = null;

    #[ORM\Column(length: 2048)]
    private ?string $description = null;

    #[ORM\Column(length: 2048)]
    private ?string $sender_name = null;

    #[ORM\Column(length: 32)]
    private ?string $sender_bank_account = null;

    #[ORM\Column(length: 4096, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'bankHistoryTransactions')]
    private ?Donor $donor = null;

    #[ORM\Column]
    private bool $is_draft = true;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $md5 = null;

    #[ORM\Column(length: 4096, nullable: true)]
    private ?string $raw = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'bankHistoryTransactions')]
    private ?self $bank_history = null;

    #[ORM\OneToMany(mappedBy: 'bank_history', targetEntity: self::class)]
    private Collection $bankHistoryTransactions;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $manual = null;

    #[ORM\Column]
    private ?bool $flagged = null;



    public function __construct()
    {
        $this->bankHistoryTransactions = new ArrayCollection();
        $this->manual = false;
        $this->flagged = false;

        // wygląda na to, że podkategoria nie jest potrzebna, ale nie chcę jej już
        // wyrzucać z aplikacji - ustawiam na sztywno jako BRAK i nigdzie jej nie używam...
        $this->sub_category = CategoryKeys::BRAK;
    }

    public static function create(): self
    {
        return new self();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->sender_name;
    }

    public function setSenderName(string $sender_name): self
    {
        $this->sender_name = $sender_name;

        return $this;
    }

    public function getSenderBankAccount(): ?string
    {
        return $this->sender_bank_account;
    }

    public function setSenderBankAccount(string $sender_bank_account): self
    {
        $this->sender_bank_account = $sender_bank_account;

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

    public function isDraft(): bool
    {
        return $this->is_draft;
    }

    public function setIsDraft(bool $is_draft): self
    {
        $this->is_draft = $is_draft;

        return $this;
    }

    public function getMd5(): ?string
    {
        return $this->md5;
    }

    public function setMd5(?string $md5): self
    {
        $this->md5 = $md5;

        return $this;
    }

    public function getRaw(): ?string
    {
        return $this->raw;
    }

    public function setRaw(?string $raw): self
    {
        $this->raw = $raw;
        $this->calculateMd5();
        return $this;
    }

    public function calculateMd5(): void
    {
        // okazało się, że czasem import danych z banku zjada np. adresata (raz jest imie i nazwisko z adresem,
        // innym razem tylko imię i nazwisko), aby temu zapobiedz do obliczania md5 stosuję:
        // data, kwota, tytuł operacji, nr konta nadawcy, nr konta odbiorcy - właściciela
        // $md5_text = DateTimeHelper::format($this->getDate());
        // $md5_text .= number_format($this->getValue(), 2);
        // $md5_text .= $this->getDescription();
        // $md5_text .= $this->getSenderBankAccount();
        if (StringHelper::isNullOrEmpty($this->getRaw())) {
            $stringUuid = Uuid::v4()->toRfc4122();
            $this->setMd5(str_replace("-", "", $stringUuid));
        }
        $this->setMd5(md5(StringHelper::trimAll($this->getRaw(), true)));
    }

    public function isValid(): bool
    {
        if (empty($this->violationList)) {
            return false;
        }
        return $this->violationList->count() == 0;
    }

    public function getValidationErrors(): mixed
    {
        if (empty($this->violationList)) {
            return [];
        }
        return $this->violationList;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        $this->validateIfCorrectCategoriesAreSet($context);
        $this->validateDonorIsSet($context);
        $this->validateKoszty($context);
        $this->validateDofinansowanie($context);

        $this->violationList = $context->getViolations();
    }

    private function belongsToCateogry(string $category, string $subCategory = null): bool
    {
        if ($subCategory === null) {
            $subCategory == $category;
        }
        return ($this->getCategory() === $category) && ($this->getSubCategory() === $subCategory);
    }

    private static function addError(ExecutionContextInterface $context, string $field, string $errorCode): void
    {
        $errorMessage = ErrorCodes::message($errorCode);
        $context->buildViolation($errorMessage)->atPath($field)->setCode($errorCode)->addViolation();
    }

    private static function errorIfEmpty($context, $field, $fieldName, $errorCode)
    {
        if (empty($field)) {
            self::addError($context, $fieldName, $errorCode);
        }
    }

    private static function errorIfNotEmpty($context, $field, $fieldName, $errorCode)
    {
        if (!empty($field)) {
            self::addError($context, $fieldName, $errorCode);
        }
    }


    private function validateIfCorrectCategoriesAreSet(ExecutionContextInterface $context): void
    {
        // Zrezygnowałem z funkcjonalności podkategorii
        // $badSubCategory = false;
        // switch ($this->getCategory()) {
        //     case CategoryKeys::BRAK:
        //         $badSubCategory = $this->getSubCategory() != CategoryKeys::BRAK;
        //         break;
        //     case CategoryKeys::KOSZTY:
        //         $badSubCategory = $this->getSubCategory() != CategoryKeys::KOSZTY;
        //         break;
        //     case CategoryKeys::DOFINANSOWANIE:
        //         $badSubCategory = $this->getSubCategory() != CategoryKeys::DOFINANSOWANIE;
        //         break;
        //     case CategoryKeys::DAROWIZNA:
        //         $badSubCategory = $this->getSubCategory() != CategoryKeys::DAROWIZNA;
        //         break;
        // }
        // if ($badSubCategory) {
        //     self::addError($context, 'sub_category', ErrorCodes::BLEDNIE_WYBRANA_PODKATEGORIA);
        // }
    }

    private function validateDonorIsSet(ExecutionContextInterface $context): void
    {
        if ($this->getCategory() == CategoryKeys::DAROWIZNA) {
            if ($this->getDonor() == null) {
                self::addError($context, 'donor', ErrorCodes::BRAK_DARCZYNCY);
            }
        }
    }

    private function validateKoszty(ExecutionContextInterface $context): void
    {
        if ($this->getCategory() == CategoryKeys::KOSZTY) {
            if ($this->getDonor() != null) {
                self::addError($context, 'category', ErrorCodes::DODATKOWE_POLA_USTAWIONE);
            }
        }
    }

    private function validateDofinansowanie(ExecutionContextInterface $context): void
    {
        if ($this->getCategory() == CategoryKeys::DOFINANSOWANIE) {
            if ($this->getDonor() != null) {
                self::addError($context, 'category', ErrorCodes::DODATKOWE_POLA_USTAWIONE);
            }
        }
    }

    public function getBankHistory(): ?self
    {
        return $this->bank_history;
    }

    public function setBankHistory(?self $bank_history): self
    {
        $this->bank_history = $bank_history;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getBankHistoryTransactions(): Collection
    {
        return $this->bankHistoryTransactions;
    }

    public function addBankHistory(self $bankHistory): self
    {
        if (!$this->bankHistoryTransactions->contains($bankHistory)) {
            $this->bankHistoryTransactions->add($bankHistory);
            $bankHistory->setBankHistory($this);
        }

        return $this;
    }

    public function removeBankHistory(self $bankHistory): self
    {
        if ($this->bankHistoryTransactions->removeElement($bankHistory)) {
            // set the owning side to null (unless already changed)
            if ($bankHistory->getBankHistory() === $this) {
                $bankHistory->setBankHistory(null);
            }
        }

        return $this;
    }

    public function isManual(): ?bool
    {
        return $this->manual;
    }

    public function setManual(bool $manual): self
    {
        $this->manual = $manual;

        return $this;
    }

    public function isFlagged(): ?bool
    {
        return $this->flagged;
    }

    public function setFlagged(bool $flagged): self
    {
        $this->flagged = $flagged;

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
}