<?php

namespace App\Entity;

use App\Constants\UserRolesKeys;
use App\Repository\UserRepository;
use App\Service\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity('email', message: "Podany adres e-mail już istnieje")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Email(message: 'Podany adres email: {{ value }} nie jest prawidłowy')]
    #[ORM\Column(length: 250, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Assert\Length(min: 8, max: 1024, minMessage: "Hasło musi zawierać przynajmniej {{ limit }} znaków", maxMessage: 'Hasło nie może zawierać więcej niż {{ limit }} znaków')]
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $isVerified = false;

    #[Assert\NotBlank(message: 'Imię nie może być puste')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[Assert\NotBlank(message: 'Nazwisko nie może być puste')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $loginSuccess = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $loginFailed = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $newSendDate = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Donor::class)]
    private Collection $donors;


    public function __construct()
    {
        $this->roles[] = UserRolesKeys::USER;
        $this->isActive = false;
        $this->isVerified = false;
        $this->password = '_NIEAKTYWNE_' . md5(date('d.m.Y H:i:s', strtotime("now")));
        $this->donors = new ArrayCollection();
    }

    public function isNew(): bool
    {
        return is_null($this->getId());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = UserRolesKeys::USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        if ($this->hasRole($role)) {
            $index = array_search($role, $this->roles);
            if ($index != false) {
                unset($this->roles[$index]);
            }
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getDisplayName()
    {
        $name = trim(join(' ', [
            $this->getFirstName(),
            $this->getLastName()
        ]));
        $email = '<' . $this->getEmail() . '>';

        if (StringHelper::isNullOrEmpty($name)) {
            return $this->email;
        }

        $displayName = trim(join(' ', [
            $name,
            $email,
        ]));
        return $displayName;
    }

    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function getLoginSuccess(): ?\DateTimeInterface
    {
        return $this->loginSuccess;
    }

    public function setLoginSuccess(?\DateTimeInterface $loginSuccess): self
    {
        $this->loginSuccess = $loginSuccess;

        return $this;
    }

    public function getLoginFailed(): ?\DateTimeInterface
    {
        return $this->loginFailed;
    }

    public function setLoginFailed(?\DateTimeInterface $loginFailed): self
    {
        $this->loginFailed = $loginFailed;

        return $this;
    }

    public function isRegistered(): bool
    {
        $firstNameIsEmpty = StringHelper::isNullOrEmpty($this->getFirstName());
        $lastNameIsEmpty = StringHelper::isNullOrEmpty($this->getLastName());
        return !($firstNameIsEmpty || $lastNameIsEmpty);
    }

    public function getNewSendDate(): ?\DateTimeInterface
    {
        return $this->newSendDate;
    }

    public function setNewSendDate(?\DateTimeInterface $newSendDate): self
    {
        $this->newSendDate = $newSendDate;

        return $this;
    }

    public function doesUserEverLoggedIn(): bool
    {
        return !is_null($this->getLoginSuccess());
    }

    /**
     * @return Collection<int, Donor>
     */
    public function getDonors(): Collection
    {
        return $this->donors;
    }

    public function addDonor(Donor $donor): static
    {
        if (!$this->donors->contains($donor)) {
            $this->donors->add($donor);
            $donor->setUser($this);
        }

        return $this;
    }

    public function removeDonor(Donor $donor): static
    {
        if ($this->donors->removeElement($donor)) {
            // set the owning side to null (unless already changed)
            if ($donor->getUser() === $this) {
                $donor->setUser(null);
            }
        }

        return $this;
    }

    private function hasValidRoles(): bool
    {
        return $this->hasRole(UserRolesKeys::ADMIN) || $this->hasRole(UserRolesKeys::DONOR);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if (!$this->hasValidRoles()) {
            $context->buildViolation('Proszę zaznaczyć przynajmniej jedną rolę.')
                ->atPath('roles')
                ->addViolation();
        }
    }
}