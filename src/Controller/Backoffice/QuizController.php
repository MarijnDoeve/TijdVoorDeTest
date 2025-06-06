<?php

declare(strict_types=1);

namespace App\Controller\Backoffice;

use App\Controller\AbstractController;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Repository\CandidateRepository;
use App\Security\Voter\SeasonVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted('ROLE_USER')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly CandidateRepository $candidateRepository,
    ) {}

    #[Route('/backoffice/season/{seasonCode}/quiz/{quiz}', name: 'app_backoffice_quiz',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function index(Season $season, Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'result' => $this->candidateRepository->getScores($quiz),
        ]);
    }

    #[Route('/backoffice/season/{seasonCode}/quiz/{quiz}/enable', name: 'app_backoffice_enable',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function enableQuiz(Season $season, ?Quiz $quiz, EntityManagerInterface $em): Response
    {
        $season->setActiveQuiz($quiz);
        $em->flush();

        if ($quiz instanceof Quiz) {
            return $this->redirectToRoute('app_backoffice_quiz', ['seasonCode' => $season->getSeasonCode(), 'quiz' => $quiz->getId()]);
        }

        return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
    }
}
