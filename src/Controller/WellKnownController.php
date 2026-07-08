<?php

declare(strict_types=1);

namespace Tvdt\Controller;

use Safe\DateTimeImmutable;
use Safe\Exceptions\DatetimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Serves well-known URIs (https://www.rfc-editor.org/rfc/rfc8615). */
final class WellKnownController extends AbstractController
{
    /** @see https://w3c.github.io/webappsec-change-password-url/ */
    #[Route('/.well-known/change-password', name: 'tvdt_well_known_change_password', methods: ['GET'])]
    public function changePassword(): RedirectResponse
    {
        return $this->redirectToRoute('tvdt_backoffice_settings');
    }

    /**
     * @see https://www.rfc-editor.org/rfc/rfc9116
     *
     * @throws DatetimeException
     */
    #[Route('/.well-known/security.txt', name: 'tvdt_well_known_security_txt', methods: ['GET'])]
    public function securityTxt(): Response
    {
        $expires = new DateTimeImmutable('+1 year')->format(\DATE_RFC3339);

        $content = <<<TXT
            Contact: https://github.com/MarijnDoeve/TijdVoorDeTest/security/advisories/new
            Expires: {$expires}
            Preferred-Languages: nl, en

            TXT;

        return new Response($content, headers: ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
