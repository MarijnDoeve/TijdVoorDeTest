<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Season;
use App\Entity\User;
use App\Repository\CandidateRepository;
use App\Repository\SeasonRepository;
use App\Security\Voter\SeasonVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[IsGranted('ROLE_USER')]
final class BackofficeController extends AbstractController
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly CandidateRepository $candidateRepository,
        private readonly Security $security,
    ) {}

    #[Route('/backoffice/', name: 'app_backoffice_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $seasons = $this->security->isGranted('ROLE_ADMIN')
            ? $this->seasonRepository->findAll()
            : $this->seasonRepository->getSeasonsForUser($user);

        return $this->render('backoffice/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }

    #[Route('/backoffice/{seasonCode}', name: 'app_backoffice_season')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function season(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
        ]);
    }

    #[Route('/backoffice/{seasonCode}/{quiz}', name: 'app_backoffice_quiz')]
    public function quiz(Season $season, Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'result' => $this->candidateRepository->getScores($quiz),
        ]);
    }

    #[Route('/backoffice/{seasonCode}/{quiz}/enable', name: 'app_backoffice_enable')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function enableQuiz(Season $season, Quiz $quiz, EntityManagerInterface $em): Response
    {
        $season->setActiveQuiz($quiz);
        $em->flush();

        return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
    }

    #[Route('/backoffice/{seasonCode}/{quiz}/yaml')]
    public function testRoute(Season $season, Quiz $quiz, SerializerInterface $serializer): Response
    {
        return new Response($serializer->serialize(\App\Resource\Quiz::fromEntity($quiz)->questions, 'yaml', ['yaml_inline' => 100, 'yaml_flags' => 0]), headers: ['Content-Type' => 'text/yaml']);
    }
}
