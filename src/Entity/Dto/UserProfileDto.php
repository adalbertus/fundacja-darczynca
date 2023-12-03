<?php

namespace App\Entity\Dto;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserProfileDto
{
    public int $id;

    #[Assert\NotBlank(message: 'Pole e-mail jest wymagane.')]
    #[Assert\Email(message: 'Podany adres email: {{ value }} nie jest prawidłowy.')]
    public string $email;

    #[Assert\NotBlank(message: 'Pole jest wymagane.')]
    public ?string $firstName;

    #[Assert\NotBlank(message: 'Pole jest wymagane.')]
    public ?string $lastName;

    #[Assert\Length(min: 8, minMessage: 'Hasło musi zawierać przynajmniej {{ limit }} znaków.', max: 1024, maxMessage: 'Hasło nie może zawierać więcej niż {{ limit }} znaków.')]
    public ?string $plainPassword;

    // nieużywane
    #[Assert\EqualTo(propertyPath: 'plainPassword', message: 'Hasła się nie zgadzają.')]
    public ?string $plainPasswordRepeat;


    public function __construct(private User $user, private UserRepository $userRepository)
    {
        $this->plainPassword = null;
        $this->plainPasswordRepeat = null; // nieużywane, patrz UserProfileFormType (RepeatedType)
    }

    public static function createFromUser(User $user, UserRepository $userRepository): UserProfileDto
    {
        $userProfile = new UserProfileDto($user, $userRepository);
        $userProfile->id = $user->getId();
        $userProfile->email = $user->getEmail();
        $userProfile->firstName = $user->getFirstName();
        $userProfile->lastName = $user->getLastName();
        return $userProfile;
    }

    public function getDisplayName()
    {
        $displayName = trim(join(' ', [
            $this->firstName,
            $this->lastName,
            "[" . $this->email . "]",
        ]));
        return $displayName;
    }

    #[Assert\Callback]
    public function emailUniqueValidation(ExecutionContextInterface $context, $payload)
    {
        // if (StringHelper::isNullOrEmpty($value)) {
        //     $context->buildViolation("Pole jest wymagane.")
        //         ->atPath('email')
        //         ->addViolation();
        //     return;
        // }

        if ($this->user->getEmail() === $this->email) {
            return;
        }

        // $emailConstraint = new Email(message: "Adres e-mail '${value}' jest nieprawidłowy.");
        // $errorList = $context->getValidator()->validate($value, $emailConstraint);
        // foreach ($errorList as $error) {
        //     $context->buildViolation($error->getMessage())
        //         ->atPath('email')
        //         ->addViolation();
        // }

        $user = $this->userRepository->findByEmail($this->email);
        if ($user) {
            $context->buildViolation("Podany adres e-mail już istnieje.")
                ->atPath('email')
                ->addViolation();
        }
    }
}