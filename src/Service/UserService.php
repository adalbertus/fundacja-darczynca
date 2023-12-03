<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EmailService $emailService,
        private ResetPasswordHelperInterface $resetPasswordHelper,

    ) {
    }


    /**
     * Utworzenie użytkowników na podstawie adresu e-mail.
     * Jeżeli użytkownik już istnieje to zostanie on wczytany zamiast być utworzonym.
     * Użytkownicy tworzeni są tylko z adresem e-mail, reszta jest pusta i wymaga uzupełnienia
     * podczas rejestracji, bez tego taki użytkownik nie będzie mógł się zalogować.
     * @param string $email     może to być pojedynczy e-mail lub kilka oddzielonych znakiem ';'
     * @param  mixed $role      domyślna rola
     * @param bool $autoFlush   czy nowo dla nowo stworzonych użytkowników wykonywać operację flush()
     * @param  mixed $firstName imię
     * @param  mixed $lastName  nazwisko
     * @return array            zwracana jest tablica użytkowników [email]['user' => user, 'new' => true|false], true gdy nowo utworzony
     */
    public function createUsersByEmailIfNeeded(?string $email, string $role = null, bool $autoFlush = false, string $firstName = null, string $lastName = null): array
    {
        $result = [];
        if (StringHelper::isNullOrEmpty($email)) {
            return $result;
        }
        $emails = explode(';', $email);

        // wyszykuję istniejących użytkowników
        $users = $this->userRepo->findBy(['email' => $emails], ['id' => 'asc']);

        $existingEmails = array_map(
            function ($user) {
                return $user->getEmail();
            },
            $users
        );
        foreach ($users as $user) {
            // przypisanie roli (jeżeli trzeba) do istniejących użytkowników
            if (!is_null($role)) {
                $user->addRole($role);
            }
            $result[$user->getEmail()] = ['user' => $user, 'new' => false];
        }

        $newEmails = array_diff($emails, $existingEmails);
        foreach ($newEmails as $email) {
            $user = new User();
            $user->setEmail($email);
            if (!StringHelper::isNullOrEmpty($firstName)) {
                $user->setFirstName($firstName);
            }
            if (!StringHelper::isNullOrEmpty($lastName)) {
                $user->setLastName($lastName);
            }
            $user->setIsActive(true);
            $user->setIsVerified(true);

            if (!is_null($role)) {
                $user->addRole($role);
            }

            $this->em->persist($user);
            $result[$user->getEmail()] = ['user' => $user, 'new' => true];
        }

        if ($autoFlush) {
            $this->em->flush();
        }

        return $result;
    }

    /**
     * Utworzenie losowego hasła i wysłanie e-mail z informacjami logowana się
     *
     * @param  mixed $user
     * @param  mixed $force - jeżeli true to wymuszone jest nowe hasło
     * @return void
     */
    public function setRandomPasswordAndSendNewAccountNotification(User $user, bool $force = false, string $templateName = 'emails/registration/confirmation_email.html.twig'): void
    {
        if (!$force) {
            if ($user->doesUserEverLoggedIn()) {
                return;
            }
        }

        $pass = $this->setRandomPassword($user);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $pass));
        $user->setNewSendDate(date_create());
        $this->em->persist($user);
        $this->em->flush();
        // wysłanie e-mail z informacją o założeniu nowego konta
        $this->emailService->sendEmailInvitation($user, $pass, $templateName);
    }

    /**
     * Utworzenie domyślnego hasła dla użytkownika, któremu nie został on już wcześniej wysłany
     * @param User $user
     * @return string
     */
    public function setRandomPassword(User $user): ?string
    {
        $pass = $this->randomPassword();
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $pass));
        $user->setNewSendDate(date_create());
        $this->em->persist($user);
        $this->em->flush();
        return $pass;
    }

    private function randomPassword()
    {
        // $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        // $pass = array(); //remember to declare $pass as an array
        // $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        // for ($i = 0; $i < $length; $i++) {
        //     $n = rand(0, $alphaLength);
        //     $pass[] = $alphabet[$n];
        // }
        // return implode($pass); //turn the array into a string

        $bytes = random_bytes(20);
        $pass = substr(md5($bytes), -10);
        return $pass;
    }

    public function sendResetPassword(User $user): ResetPasswordToken
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $this->emailService->sendPasswordResetEmail($user, $resetToken);
        return $resetToken;
    }
}