<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\Backoffice\BackofficeController;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\User;

#[CoversClass(BackofficeController::class)]
final class BackofficeControllerTest extends WebTestCase
{
    public function testExportQuizFilenameIsSanitized(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user2@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $quiz = $entityManager->getRepository(Quiz::class)->findOneBy(['name' => 'Quiz 1']);
        $this->assertInstanceOf(Quiz::class, $quiz);

        $client->request(Request::METHOD_GET, \sprintf('/backoffice/quiz/%s/export', $quiz->id));

        self::assertResponseIsSuccessful();
        $disposition = (string) $client->getResponse()->headers->get('Content-Disposition');
        $this->assertStringContainsString('filename=Quiz-1.xlsx', $disposition);
        $this->assertStringNotContainsString('Quiz 1.xlsx', $disposition);
    }
}
