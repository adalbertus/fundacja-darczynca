<?php

namespace App\Controller;

use App\Constants\ErrorCodes;
use App\Constants\UserRolesKeys;
use App\Entity\User;
use App\Entity\Dto\UserProfileDto;
use App\Form\UserProfileFormType;
use App\Repository\UserRepository;
use App\Service\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserProfileController extends BaseController
{
    #[IsGranted(UserRolesKeys::USER, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/profile', name: 'app_user_profile')]
    public function details(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if ($user == null) {
            $this->redirectToRoute('app_login');
        }
        $userProfile = UserProfileDto::createFromUser($user, $userRepository);
        $form = $this->createForm(
            UserProfileFormType::class,
            $userProfile,
        );

        return $this->render(
            'user_profile/details.html.twig',
            [
                'row' => $user,
                'form' => $form
            ]
        );
    }

    #[IsGranted(UserRolesKeys::USER, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/profile/update', name: 'app_user_profile_update')]
    public function update(Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        return $this->updateOrRegister(false, $request, $em, $userRepository, $userPasswordHasher);
    }


    #[IsGranted(UserRolesKeys::USER, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/profile/register', name: 'app_user_profile_register')]
    public function register(Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        return $this->updateOrRegister(true, $request, $em, $userRepository, $userPasswordHasher);
    }


    private function updateOrRegister(bool $register, Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user == null) {
            $this->redirectToRoute('app_login');
        }

        if ($register && $user->isRegistered()) {
            $this->addFlashWarning("Wygląda na to, że użytkownik <strong>" . $user->getDisplayName() . "</strong> jest już zarejestrowany.<br />Być może zapomniałeś się wylogować?");
            return $this->redirectToRoute('app_homepage');
        }

        // pobieram użytkownika z bazy danych, żeby nie modyfikować obiektu app.user
        // w przeciwnym razie np. modyfikacja e-mail zmieniała go od razu w nagłówku
        // nawet jak był nieprawidłowy
        $userProfile = UserProfileDto::createFromUser($user, $userRepository);

        $form = $this->createForm(
            UserProfileFormType::class,
            $userProfile,
            ['password_required' => $register]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();
            $user->setFirstName($userProfile->firstName);
            $user->setLastName($userProfile->lastName);
            $user->setEmail($userProfile->email);

            $plainPassword = $form->get('plainPassword')->getData();
            if (!StringHelper::isNullOrEmpty($plainPassword)) {
                $encodedPassword = $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($encodedPassword);
            }
            $em->persist($user);
            $em->flush();

            $this->addFlashSuccess('Profil został zaktualizowany');

            return $this->redirectToRoute('app_homepage');
        }
        return $this->render(
            'user_profile/update_register.html.twig',
            [
                'row' => $userProfile,
                'form' => $form,
                'register' => $register
            ]
        );
    }
}