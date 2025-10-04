<?php

declare(strict_types=1);

namespace Tvdt\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Controller\AbstractController;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Exception\ErrorClearingQuizException;
use Tvdt\Repository\CandidateRepository;
use Tvdt\Repository\QuizCandidateRepository;
use Tvdt\Repository\QuizRepository;
use Tvdt\Security\Voter\SeasonVoter;

#[AsController]
#[IsGranted('ROLE_USER')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly CandidateRepository $candidateRepository,
        private readonly TranslatorInterface $translator,
    ) {}

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}',
        name: 'tvdt_backoffice_quiz',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function index(Season $season, Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'result' => $this->candidateRepository->getScores($quiz),
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/enable',
        name: 'tvdt_backoffice_enable',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID.'|null'],
    )]
    public function enableQuiz(Season $season, ?Quiz $quiz, EntityManagerInterface $em): RedirectResponse
    {
        $season->setActiveQuiz($quiz);
        $em->flush();

        if ($quiz instanceof Quiz) {
            return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $season->getSeasonCode(), 'quiz' => $quiz->getId()]);
        }

        return $this->redirectToRoute('tvdt_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/clear',
        name: 'tvdt_backoffice_quiz_clear',
        requirements: ['quiz' => Requirement::UUID],
    )]
    public function clearQuiz(Quiz $quiz, QuizRepository $quizRepository): RedirectResponse
    {
        try {
            $quizRepository->clearQuiz($quiz);
            $this->addFlash('success', $this->translator->trans('Quiz cleared'));
        } catch (ErrorClearingQuizException) {
            $this->addFlash('error', $this->translator->trans('Error clearing quiz'));
        }

        return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $quiz->getSeason()->getSeasonCode(), 'quiz' => $quiz->getId()]);
    }

    #[IsGranted(SeasonVoter::DELETE, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/delete',
        name: 'tvdt_backoffice_quiz_delete',
        requirements: ['quiz' => Requirement::UUID],
    )]
    public function deleteQuiz(Quiz $quiz, QuizRepository $quizRepository): RedirectResponse
    {
        $quizRepository->deleteQuiz($quiz);

        $this->addFlash('success', $this->translator->trans('Quiz deleted'));

        return $this->redirectToRoute('tvdt_backoffice_season', ['seasonCode' => $quiz->getSeason()->getSeasonCode()]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/candidate/{candidate}/modify_correction',
        name: 'tvdt_backoffice_modify_correction',
        requirements: ['quiz' => Requirement::UUID, 'candidate' => Requirement::UUID],
    )]
    public function modifyCorrection(Quiz $quiz, Candidate $candidate, QuizCandidateRepository $quizCandidateRepository, Request $request): RedirectResponse
    {
        if (!$request->isMethod('POST')) {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        $corrections = (float) $request->request->get('corrections');

        $quizCandidateRepository->setCorrectionsForCandidate($quiz, $candidate, $corrections);

        return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $quiz->getSeason()->getSeasonCode(), 'quiz' => $quiz->getId()]);
    }
}
