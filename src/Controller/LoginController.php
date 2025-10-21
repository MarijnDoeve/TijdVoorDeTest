<?php

declare(strict_types=1);

namespace Tvdt\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Enum\FlashType;

#[AsController]
final class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'tvdt_login_login')]
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error instanceof AuthenticationException) {
            $this->addFlash(FlashType::Danger, $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security'));
        }

        return $this->render('backoffice/login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'tvdt_login_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
