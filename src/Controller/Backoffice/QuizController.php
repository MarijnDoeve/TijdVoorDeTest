<?php

declare(strict_types=1);

namespace App\Controller\Backoffice;

use App\Controller\AbstractController;
use App\Entity\Candidate;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Repository\CandidateRepository;
use App\Repository\QuizCandidateRepository;
use App\Security\Voter\SeasonVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted('ROLE_USER')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly CandidateRepository $candidateRepository,
    ) {}

    #[Route(
        '/backoffice/season/{seasonCode}/quiz/{quiz}',
        name: 'app_backoffice_quiz',
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

    #[Route(
        '/backoffice/season/{seasonCode}/quiz/{quiz}/enable',
        name: 'app_backoffice_enable',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function enableQuiz(Season $season, ?Quiz $quiz, EntityManagerInterface $em): RedirectResponse
    {
        $season->setActiveQuiz($quiz);
        $em->flush();

        if ($quiz instanceof Quiz) {
            return $this->redirectToRoute('app_backoffice_quiz', ['seasonCode' => $season->getSeasonCode(), 'quiz' => $quiz->getId()]);
        }

        return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
    }

    #[Route(
        '/backoffice/quiz/{quiz}/modify_correction/{candidate}',
        name: 'app_backoffice_modify_correction',
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    public function modifyCorrection(Quiz $quiz, Candidate $candidate, QuizCandidateRepository $quizCandidateRepository, Request $request): RedirectResponse
    {
        if (!$request->isMethod('POST')) {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        $corrections = (float) $request->request->get('corrections');

        $quizCandidateRepository->setCorrectionsForCandidate($quiz, $candidate, $corrections);

        return $this->redirectToRoute('app_backoffice_quiz', ['seasonCode' => $quiz->getSeason()->getSeasonCode(), 'quiz' => $quiz->getId()]);
    }
}
