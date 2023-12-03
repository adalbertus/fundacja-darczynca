<?php
namespace App\Service;

use App\Constants\SettingsKeys;
use App\Entity\User;
use App\Repository\BankHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class NotificationService
{
    public function __construct(
        private BankHistoryRepository $bankHistoryRepository,
        private EntityManagerInterface $entityManagerInterface,
        private UserService $userService,
        private UrlGeneratorInterface $router,
        private MailerInterface $mailer,
        private ContainerBagInterface $params,
    ) {
    }


    public function sendNewUserAccountCreatedIfNeeded(User $user, bool $generateNewPassword = true): void
    {
        if ($user == null) {
            return;
        }

        $newPass = '';
        if ($generateNewPassword) {
            $newPass = $this->userService->setRandomPassword($user);
        }

        $appName = $this->params->get(SettingsKeys::APP_NAME);
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject("{$appName} - dostęp do aplikacji")
            ->htmlTemplate('emails/member/new_email.html.twig')
            ->context([
                'newPass' => $newPass,
                'homepageUrl' => $this->router->generate('app_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'registerUrl' => $this->router->generate('app_user_profile_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'user' => $user,
            ])
        ;

        $this->mailer->send($email);
    }

}