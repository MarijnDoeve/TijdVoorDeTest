<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Season;
use App\Repository\CandidateRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class BackofficeController extends AbstractController
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly CandidateRepository $candidateRepository,
    ) {}

    #[Route('/backoffice/', name: 'index')]
    public function index(): Response
    {
        $seasons = $this->seasonRepository->findAll();

        return $this->render('backoffice/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }

    #[Route('/backoffice/{seasonCode}', name: 'season')]
    public function season(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
        ]);
    }

    #[Route('/backoffice/{seasonCode}/{quiz}', name: 'quiz')]
    public function quiz(Season $season, Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'result' => $this->candidateRepository->getScores($quiz),
        ]);
    }
}
