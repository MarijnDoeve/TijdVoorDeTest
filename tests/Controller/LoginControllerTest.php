<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\LoginController;

#[CoversClass(LoginController::class)]
final class LoginControllerTest extends AbstractControllerWebTestCase
{
    public function testLoginPageLoadsWhenNotAuthenticated(): void
    {
        $this->client->request(Request::METHOD_GET, '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testNavbarTogglerHasNoDeadTargetWhenNotAuthenticated(): void
    {
        $this->client->request(Request::METHOD_GET, '/login');

        $crawler = $this->client->getCrawler();
        $toggler = $crawler->filter('.navbar-toggler');

        if (0 === $toggler->count()) {
            self::assertSelectorNotExists('#navbarSupportedContent');

            return;
        }

        $target = $toggler->attr('data-bs-target');
        $this->assertNotNull($target);
        self::assertSelectorExists($target);
    }

    public function testLoginRedirectsToBackofficeWhenAlreadyAuthenticated(): void
    {
        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_GET, '/login');

        self::assertResponseRedirects('/backoffice/');
    }

    public function testLoginWithInvalidCredentialsShowsFlash(): void
    {
        $this->client->request(Request::METHOD_GET, '/login');
        $form = $this->client->getCrawler()->filter('form')->form([
            '_username' => 'test@example.org',
            '_password' => 'wrong-password',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('body', 'Ongeldige inloggegevens.');
    }

    public function testLogoutIsInterceptedByFirewall(): void
    {
        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_GET, '/logout');

        self::assertResponseRedirects();
    }
}
