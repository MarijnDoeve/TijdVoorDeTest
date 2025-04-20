<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Season;
use App\Entity\User;
use App\Repository\CandidateRepository;
use App\Repository\SeasonRepository;
use App\Security\Voter\SeasonVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
}
