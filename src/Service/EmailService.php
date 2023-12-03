<?php

namespace App\Service;

use App\Constants\SettingsKeys;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class EmailService
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $router,
        private ContainerBagInterface $params,
    ) {
    }

    public function sendEmailInvitation(UserInterface $user, string $plainPassword, string $templateName = 'emails/registration/confirmation_email.html.twig'): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Błędny argument: userInterface powinien być klasy User');
        }
        /** @var User $user */
        // $signatureComponents = $this->verifyEmailHelper->generateSignature(
        //     'app_register',
        //     $user->getId(),
        //     $user->getEmail()
        // );
        $appName = $this->params->get(SettingsKeys::APP_NAME);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject("{$appName} - konto zostało utworzone")
            ->htmlTemplate($templateName)
        ;

        $context = $email->getContext();
        $context['user'] = $user;
        $context['registerUrl'] = $this->router->generate('app_user_profile_register', [], UrlGeneratorInterface::ABSOLUTE_URL);
        // $context['signedUrl'] = $signatureComponents->getSignedUrl();
        // $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        // $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();
        $context['plainPassword'] = $plainPassword;

        $email->context($context);

        $this->mailer->send($email);
    }

    public function sendEmailConfirmation2(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Błędny argument: userInterface powinien być klasy User');
        }
        /** @var User $user */

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail()
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Błędny argument: userInterface powinien być klasy User');
        }
        /** @var User $user */

        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function sendPasswordResetEmail(UserInterface $user, ResetPasswordToken $resetToken)
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Błędny argument: userInterface powinien być klasy User');
        }
        /** @var User $user */

        $appName = $this->params->get(SettingsKeys::APP_NAME);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject("${appName} - prośba o zresetowanie hasła")
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'first_name' => $user->getFirstName(),
            ])
        ;

        $this->mailer->send($email);
    }
}