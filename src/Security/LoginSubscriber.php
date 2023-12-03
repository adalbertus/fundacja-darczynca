<?php

namespace App\Security;

use App\Service\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\Type\VoidType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private EntityManagerInterface $em
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure'
        ];
    }


    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $user->setLoginSuccess(date_create());
        $this->em->persist($user);
        $this->em->flush();

        if (StringHelper::isNullOrEmpty($user->getFirstName()) || StringHelper::isNullOrEmpty($user->getLastName())) {
            // użytkownik nie ma wprowadzonego imienia i nazwiska dlatego trzeba przekierować go do
            // strony z rejestracją tych danych
            $registerUrl = $this->router->generate('app_user_profile_register');
            $event->setResponse(new RedirectResponse($registerUrl));
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $user = $event->getPassport()->getUser();
        $user->setLoginFailed(date_create());
        $this->em->persist($user);
        $this->em->flush();
    }

}