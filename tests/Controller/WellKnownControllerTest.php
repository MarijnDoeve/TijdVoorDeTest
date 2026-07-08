<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\WellKnownController;

#[CoversClass(WellKnownController::class)]
final class WellKnownControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testChangePasswordRedirectsToSettings(): void
    {
        $this->client->request(Request::METHOD_GET, '/.well-known/change-password');

        self::assertResponseRedirects('/backoffice/settings');
    }

    public function testSecurityTxt(): void
    {
        $this->client->request(Request::METHOD_GET, '/.well-known/security.txt');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8');

        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Contact:', $content);
        $this->assertStringContainsString('Expires:', $content);
    }
}
