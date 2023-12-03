<?php

namespace App\Controller;

use App\Constants\ErrorCodes;
use App\Constants\UserRolesKeys;
use App\Entity\User;
use App\Form\UserCreateFormType;
use App\Form\UserUpdateFormType;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use App\Service\StringHelper;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class UserController extends BaseController
{
    use ResetPasswordControllerTrait;
    use PagerTriat;


    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/users', name: 'app_users')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $queryCriteria = $this->prepareCriteria($request->query);
        $queryBuilder = $userRepository->getPagerQueryBuilder($queryCriteria);
        $pager = $this->getPager($queryBuilder, $request);

        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'pager' => $pager,
            'totalRows' => $pager->getNbResults(),
        ]);
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/user/create', name: 'app_user_create')]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user->setIsActive(true);

        return $this->_createOrUpdate($user, $request, $em, $passwordHasher);
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/user/{id<\d+>}/update', name: 'app_user_update')]
    public function update(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        return $this->_createOrUpdate($user, $request, $em, $passwordHasher);
    }

    private function _createOrUpdate(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserCreateFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if ($user->isNew()) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, 'haslo do zmiany')
                );
            }
            $em->persist($user);
            $em->flush();
            if ($user->isNew()) {
                $this->addFlashSuccess('Użytkownik <strong>' . $user->getFirstName() . ' ' . $user->getLastName() . '</strong> został utworzony.');
            } else {
                $this->addFlashSuccess('Użytkownik <strong>' . $user->getFirstName() . ' ' . $user->getLastName() . '</strong> został zaktualizowany.');
            }
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/create_update.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/user/{id<\d+>}/send-registration', name: 'app_user_send_registration')]
    public function send_registation(User $user, EntityManagerInterface $em, UserService $userService, Request $request): Response
    {
        if ($user) {
            $userService->setRandomPasswordAndSendNewAccountNotification($user);
            $this->addFlashInfo('E-mail z informacją o założonym koncie i z nowym hasłem został wysłany do <strong>' . $user->getEmail() . '</strong>.');
        } else {
            $this->addFlashError('Nie znaleziono użytkownika o podanym identyfikatorze');
        }
        return $this->redirectToRoute('app_users');
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/user/{id<\d+>}/details', name: 'app_user_details')]
    public function details(User $user, EntityManagerInterface $em, UserService $userService, Request $request): Response
    {
        $form = $this->createForm(UserCreateFormType::class, $user);

        return $this->render('user/details.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/user/{id<\d+>}/delete', name: 'app_user_delete')]
    public function delete(User $user, EntityManagerInterface $em, UserService $userService, Request $request): Response
    {
        $form = $this->createForm(UserCreateFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        }

        return $this->render('user/delete.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/user/{id<\d+>}/force-password-reset', name: 'app_user_send_password_reset')]
    public function forcePasswordReset(User $user, UserService $userService, Request $request): Response
    {
        if ($user) {
            try {
                $resetToken = $userService->sendResetPassword($user);
                $this->setTokenObjectInSession($resetToken);

                $this->addFlashInfo('Hasło dla konta <strong>' . $user->getEmail() . '</strong> zostało zresetowane. E-mail z informacją o tym fakcie wysłany.');
            } catch (ResetPasswordExceptionInterface $e) {
                $this->addFlashError($e->getReason());
            }
        } else {
            $this->addFlashError('Nie znaleziono użytkownika o podanym identyfikatorze');
        }

        return $this->redirectToRoute('app_users');
    }

    // #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[IsGranted(UserVoter::SEARCH, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/api/users/{emailOnly<\d>}', name: 'app_api_users')]
    public function autocomplete_user(Request $request, UserRepository $userRepository, bool $emailOnly = false, ): Response
    {
        $pattern = $request->query->get('query', '');
        $result = [];
        $users = $userRepository->findByPattern($pattern);
        foreach ($users as $user) {
            if (StringHelper::isNullOrEmpty($user['first_name'] . $user['last_name'])) {
                $text = $user['email'];
            } else {
                $text = join(' ', [
                    $user['first_name'],
                    $user['last_name'],
                    '[' . $user['email'] . ']',
                ]);
            }
            if ($emailOnly) {
                $text = $user['email'];
            }
            $result[] = [
                'value' => $user['email'],
                'text' => $text,
            ];
        }

        return $this->json([
            'results' => $result
        ]);
    }
}