<?php

namespace App\Controller;

use App\Constants\ErrorCodes;
use App\Constants\UserRolesKeys;
use App\Form\RegistrationFormType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;


#[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
class RegistrationController extends BaseController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    // #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // funkcjonalność zastąpiona przez UserProfileController
        $this->addFlashError('funkcjonalność zastąpiona przez UserProfileController');
        return $this->redirectToRoute('app_homepage');


        $user = $this->getUser();
        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->isRegistered()) {
            $this->addFlashWarning("Wygląda na to, że użytkownik <strong>" . $user->getDisplayName() . "</strong> jest już zarejestrowany.<br />Być może zapomniałeś się wylogować?");
            return $this->redirectToRoute('app_homepage');
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlashInfo("Rejestracja zakończona.");
            // generate a signed url and email it to the user
            // $this->emailService->sendEmailConfirmation(
            //     'app_verify_email',
            //     $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('mailer@your-domain.com', 'Acme Mail Bot'))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('emails/registration/confirmation_email.html.twig')
            // );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    // #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailService->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}