<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Tvdt\Controller\ResetPasswordController;
use Tvdt\Entity\User;

#[CoversClass(ResetPasswordController::class)]
final class ResetPasswordControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testRequestPageLoads(): void
    {
        $this->client->request(Request::METHOD_GET, '/reset-password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testRequestWithUnknownEmailRedirectsToCheckEmail(): void
    {
        $this->client->request(Request::METHOD_GET, '/reset-password');
        $form = $this->client->getCrawler()->filter('form')->form([
            'reset_password_request_form[email]' => 'unknown@example.org',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/reset-password/check-email');
    }

    public function testRequestWithKnownEmailRedirectsToCheckEmail(): void
    {
        $this->client->request(Request::METHOD_GET, '/reset-password');
        $form = $this->client->getCrawler()->filter('form')->form([
            'reset_password_request_form[email]' => 'test@example.org',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/reset-password/check-email');
    }

    public function testCheckEmailPageLoads(): void
    {
        $this->client->request(Request::METHOD_GET, '/reset-password/check-email');

        $this->assertResponseIsSuccessful();
    }

    public function testResetWithInvalidTokenRedirectsToRequest(): void
    {
        $this->client->request(Request::METHOD_GET, '/reset-password/reset/invalidtoken');
        $this->client->followRedirect();

        $this->assertResponseRedirects('/reset-password');
    }

    public function testFullResetFlow(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.org']);
        $this->assertInstanceOf(User::class, $user);

        /** @var ResetPasswordHelperInterface $helper */
        $helper = self::getContainer()->get(ResetPasswordHelperInterface::class);
        $resetToken = $helper->generateResetToken($user);

        $this->client->request(Request::METHOD_GET, '/reset-password/reset/'.$resetToken->getToken());
        $this->assertResponseRedirects('/reset-password/reset');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form')->form([
            'change_password_form[plainPassword][first]' => 'NewPass123!',
            'change_password_form[plainPassword][second]' => 'NewPass123!',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/backoffice/');

        $this->entityManager->clear();
        $updatedUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.org']);
        $this->assertInstanceOf(User::class, $updatedUser);

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($updatedUser, 'NewPass123!'));
    }
}
