<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Season;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/backoffice', name: 'backoffice_')]
final class BackofficeController extends AbstractController
{
    public function __construct(private readonly SeasonRepository $seasonRepository)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $seasons = $this->seasonRepository->findAll();

        return $this->render('backoffice/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }

    #[Route('/{seasonCode}', name: 'season')]
    public function season(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
        ]);
    }

    #[Route('/{seasonCode}/{quiz}', name: 'quiz')]
    public function quiz(Season $season, Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
        ]);
    }
}
