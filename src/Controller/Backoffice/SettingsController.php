<?php

declare(strict_types=1);

namespace Tvdt\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Controller\AbstractController;
use Tvdt\Entity\User;
use Tvdt\Enum\FlashType;
use Tvdt\Form\ChangeEmailFormType;
use Tvdt\Form\ChangeUserPasswordFormType;
use Tvdt\Repository\UserRepository;
use Tvdt\Security\EmailVerifier;

final class SettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly EmailVerifier $emailVerifier,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/backoffice/settings', name: 'tvdt_backoffice_settings', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderSettings();
    }

    #[IsCsrfTokenValid('settings_language')]
    #[Route('/backoffice/settings/language', name: 'tvdt_backoffice_settings_language', methods: ['POST'])]
    public function saveLanguage(): RedirectResponse
    {
        // Only Dutch is available for now, so saving is a noop.
        $this->addFlash(FlashType::Success, $this->translator->trans('Language saved'));

        return $this->redirectToRoute('tvdt_backoffice_settings');
    }

    #[Route('/backoffice/settings/password', name: 'tvdt_backoffice_settings_password', methods: ['POST'])]
    public function changePassword(Request $request): Response
    {
        $user = $this->getSettingsUser();
        $form = $this->createForm(ChangeUserPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->password = $this->passwordHasher->hashPassword($user, $plainPassword);
            $this->entityManager->flush();

            $this->security->login($user, 'form_login', 'main');
            $this->addFlash(FlashType::Success, $this->translator->trans('Your password has been changed.'));

            return $this->redirectToRoute('tvdt_backoffice_settings');
        }

        return $this->renderSettings(passwordForm: $form, status: Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/backoffice/settings/email', name: 'tvdt_backoffice_settings_email', methods: ['POST'])]
    public function changeEmail(Request $request): Response
    {
        $user = $this->getSettingsUser();
        $form = $this->createForm(ChangeEmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            if ($this->userRepository->findOneBy(['email' => $email]) instanceof User) {
                $form->get('email')->addError(new FormError($this->translator->trans('There is already an account with this email')));

                return $this->renderSettings(emailForm: $form, status: Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user->email = $email;
            $user->isVerified = false;
            $this->entityManager->flush();

            $this->sendConfirmationEmail($user);

            $this->security->login($user, 'form_login', 'main');
            $this->addFlash(FlashType::Success, $this->translator->trans('Your email address has been changed. Please check your inbox to confirm it.'));

            return $this->redirectToRoute('tvdt_backoffice_settings');
        }

        return $this->renderSettings(emailForm: $form, status: Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[IsCsrfTokenValid('resend_confirmation')]
    #[Route('/backoffice/settings/resend-confirmation', name: 'tvdt_backoffice_settings_resend_confirmation', methods: ['POST'])]
    public function resendConfirmationEmail(): RedirectResponse
    {
        $user = $this->getSettingsUser();

        if ($user->isVerified) {
            $this->addFlash(FlashType::Info, $this->translator->trans('Your email address is already confirmed.'));

            return $this->redirectToRoute('tvdt_backoffice_settings');
        }

        $this->sendConfirmationEmail($user);
        $this->addFlash(FlashType::Success, $this->translator->trans('A new confirmation email has been sent. Please check your inbox.'));

        return $this->redirectToRoute('tvdt_backoffice_settings');
    }

    #[IsCsrfTokenValid('delete_account')]
    #[Route('/backoffice/settings/delete', name: 'tvdt_backoffice_settings_delete', methods: ['POST'])]
    public function deleteAccount(Request $request): Response
    {
        $user = $this->getSettingsUser();
        $password = (string) $request->request->get('password', '');

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('Wrong password, your account has not been deleted.'));

            return $this->redirectToRoute('tvdt_backoffice_settings');
        }

        $this->userRepository->deleteUser($user);

        return $this->security->logout(false) ?? $this->redirectToRoute('tvdt_login_login');
    }

    private function sendConfirmationEmail(User $user): void
    {
        try {
            $this->emailVerifier->sendEmailConfirmation('tvdt_verify_email', $user,
                new TemplatedEmail()
                    ->to($user->email)
                    ->subject($this->translator->trans('Please Confirm your Email'))
                    ->htmlTemplate('backoffice/registration/confirmation_email.html.twig'),
            );
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error($transportException->getMessage());
        }
    }

    private function getSettingsUser(): User
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        return $user;
    }

    /**
     * @param FormInterface<array{currentPassword: string, plainPassword: string}|null>|null $passwordForm
     * @param FormInterface<array{email: string}|null>|null                                  $emailForm
     */
    private function renderSettings(?FormInterface $passwordForm = null, ?FormInterface $emailForm = null, int $status = Response::HTTP_OK): Response
    {
        return $this->render('backoffice/settings/index.html.twig', [
            'passwordForm' => $passwordForm ?? $this->createForm(ChangeUserPasswordFormType::class),
            'emailForm' => $emailForm ?? $this->createForm(ChangeEmailFormType::class),
        ], new Response(status: $status));
    }
}
