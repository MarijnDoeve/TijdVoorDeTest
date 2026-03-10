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
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Entity\Season;
use Tvdt\Exception\ErrorClearingQuizException;
use Tvdt\Repository\QuizCandidateRepository;
use Tvdt\Repository\QuizRepository;
use Tvdt\Security\Voter\SeasonVoter;

#[AsController]
#[IsGranted('ROLE_USER')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly QuizRepository $quizRepository,
        private readonly TranslatorInterface $translator,
        private readonly QuizCandidateRepository $quizCandidateRepository,
    ) {}

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}',
        name: 'tvdt_backoffice_quiz',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function index(Season $season, Quiz $quiz): Response
    {
        return $this->redirectToRoute('tvdt_backoffice_quiz_overview', ['seasonCode' => $season->seasonCode, 'quiz' => $quiz->id]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/overview',
        name: 'tvdt_backoffice_quiz_overview',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function overview(Season $season, Quiz $quiz): Response
    {
        $fetchedQuiz = $this->quizRepository->fetchWithQuestionsAndCandidates($quiz->id);

        // Create indexed lookup for quiz candidates by candidate ID
        $quizCandidatesByCandidateId = [];
        foreach ($fetchedQuiz->candidateData as $qc) {
            $quizCandidatesByCandidateId[$qc->candidate->id->toString()] = $qc;
        }

        // Get given answers counts efficiently via database query
        $givenAnswersCountByCandidateId = $this->quizRepository->getGivenAnswersCountPerCandidate($quiz);

        // Pre-compute candidate data to avoid nested loops in template
        $candidateData = [];
        foreach ($season->candidates as $candidate) {
            $candidateIdString = $candidate->id->toString();
            $candidateData[] = [
                'candidate' => $candidate,
                'quizCandidate' => $quizCandidatesByCandidateId[$candidateIdString] ?? null,
                'givenAnswersCount' => $givenAnswersCountByCandidateId[$candidateIdString] ?? 0,
            ];
        }

        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $fetchedQuiz,
            'questionErrors' => $fetchedQuiz->getQuestionErrors(),
            'candidateData' => $candidateData,
            'activeTab' => 'overview',
            'template' => 'backoffice/quiz/tab_overview.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/result',
        name: 'tvdt_backoffice_quiz_result',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function result(Season $season, Quiz $quiz): Response
    {
        $fetchedQuiz = $this->quizRepository->fetchWithQuestions($quiz->id);

        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $fetchedQuiz,
            'result' => $this->quizRepository->getScores($quiz),
            'activeTab' => 'result',
            'template' => 'backoffice/quiz/tab_result.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/candidates-list',
        name: 'tvdt_backoffice_quiz_candidates_tab',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function candidatesTab(Season $season, Quiz $quiz): Response
    {
        // Create indexed lookup for quiz candidates by candidate ID
        $quizCandidatesByCandidateId = [];
        foreach ($quiz->candidateData as $qc) {
            $quizCandidatesByCandidateId[$qc->candidate->id->toString()] = $qc;
        }

        // Get given answers counts efficiently via database query
        $givenAnswersCountByCandidateId = $this->quizRepository->getGivenAnswersCountPerCandidate($quiz);

        // Pre-compute candidate data to avoid nested loops in template
        $candidateData = [];
        foreach ($season->candidates as $candidate) {
            $candidateIdString = $candidate->id->toString();
            $candidateData[] = [
                'candidate' => $candidate,
                'quizCandidate' => $quizCandidatesByCandidateId[$candidateIdString] ?? null,
                'givenAnswersCount' => $givenAnswersCountByCandidateId[$candidateIdString] ?? 0,
            ];
        }

        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'candidateData' => $candidateData,
            'activeTab' => 'candidates',
            'template' => 'backoffice/quiz/tab_candidates_list.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/answer-mapping',
        name: 'tvdt_backoffice_quiz_candidates',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function answerMapping(Season $season, Quiz $quiz): Response
    {
        $fetchedQuiz = $this->quizRepository->fetchWithQuestions($quiz->id);
        \assert($fetchedQuiz->questions->count() > 0);
        $firstQuestion = $fetchedQuiz->questions->first();
        \assert($firstQuestion instanceof Question);

        return $this->redirectToRoute('tvdt_backoffice_quiz_candidates_question', [
            'seasonCode' => $season->seasonCode,
            'quiz' => $quiz->id,
            'question' => $firstQuestion->id,
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/candidates/{question}',
        name: 'tvdt_backoffice_quiz_candidates_question',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
        methods: ['GET'],
    )]
    public function candidates_question(Season $season, Quiz $quiz, Question $question): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'question' => $question,
            'candidates' => $season->candidates,
            'activeTab' => 'answers',
            'template' => 'backoffice/quiz/tab_candidates.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/candidates/{question}',
        name: 'tvdt_backoffice_quiz_candidates_question_save',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
        methods: ['POST'],
    )]
    public function saveCandidateAnswers(Season $season, Quiz $quiz, Question $question, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $candidateAnswers = $request->request->all('candidate_answer');

        // Clear existing candidate-answer associations for this question
        foreach ($question->answers as $answer) {
            $answer->candidates->clear();
        }

        // Add new associations
        foreach ($candidateAnswers as $candidateId => $answerIds) {
            $candidate = $em->getRepository(Candidate::class)->find($candidateId);

            foreach ((array) $answerIds as $answerId) {
                $answer = $em->getRepository(Answer::class)->find($answerId);
                if ($answer && $candidate) {
                    $answer->addCandidate($candidate);
                }
            }
        }

        $em->flush();

        $this->addFlash('success', $this->translator->trans('Candidate answers saved'));

        return $this->redirectToRoute('tvdt_backoffice_quiz_candidates_question', [
            'seasonCode' => $season->seasonCode,
            'quiz' => $quiz->id,
            'question' => $question->id,
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
        $season->activeQuiz = $quiz;
        $em->flush();

        if ($quiz instanceof Quiz) {
            return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $season->seasonCode, 'quiz' => $quiz->id]);
        }

        return $this->redirectToRoute('tvdt_backoffice_season', ['seasonCode' => $season->seasonCode]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/clear',
        name: 'tvdt_backoffice_quiz_clear',
        requirements: ['quiz' => Requirement::UUID],
    )]
    public function clearQuiz(Quiz $quiz): RedirectResponse
    {
        try {
            $this->quizRepository->clearQuiz($quiz);
            $this->addFlash('success', $this->translator->trans('Quiz cleared'));
        } catch (ErrorClearingQuizException) {
            $this->addFlash('error', $this->translator->trans('Error clearing quiz'));
        }

        return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $quiz->season->seasonCode, 'quiz' => $quiz->id]);
    }

    #[IsGranted(SeasonVoter::DELETE, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/delete',
        name: 'tvdt_backoffice_quiz_delete',
        requirements: ['quiz' => Requirement::UUID],
    )]
    public function deleteQuiz(Quiz $quiz): RedirectResponse
    {
        $this->quizRepository->deleteQuiz($quiz);

        $this->addFlash('success', $this->translator->trans('Quiz deleted'));

        return $this->redirectToRoute('tvdt_backoffice_season', ['seasonCode' => $quiz->season->seasonCode]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/candidate/{candidate}/modify_correction',
        name: 'tvdt_backoffice_modify_correction',
        requirements: ['quiz' => Requirement::UUID, 'candidate' => Requirement::UUID],
    )]
    public function modifyCorrection(Quiz $quiz, Candidate $candidate, Request $request): RedirectResponse
    {
        if (!$request->isMethod('POST')) {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        $corrections = (float) $request->request->get('corrections');

        $this->quizCandidateRepository->setCorrectionsForCandidate($quiz, $candidate, $corrections);

        return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $quiz->season->seasonCode, 'quiz' => $quiz->id]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/candidate/{candidate}/modify_penalty',
        name: 'tvdt_backoffice_modify_penalty',
        requirements: ['quiz' => Requirement::UUID, 'candidate' => Requirement::UUID],
    )]
    public function modifyPenalty(Quiz $quiz, Candidate $candidate, Request $request): RedirectResponse
    {
        if (!$request->isMethod('POST')) {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        $penalty = (int) $request->request->get('penalty');

        $this->quizCandidateRepository->setPenaltyForCandidate($quiz, $candidate, $penalty);

        return $this->redirectToRoute('tvdt_backoffice_quiz', ['seasonCode' => $quiz->season->seasonCode, 'quiz' => $quiz->id]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'quiz')]
    #[Route(
        '/backoffice/quiz/{quiz}/candidate/{candidate}/toggle',
        name: 'tvdt_backoffice_toggle_candidate',
        requirements: ['quiz' => Requirement::UUID, 'candidate' => Requirement::UUID],
    )]
    public function toggleCandidate(Quiz $quiz, Candidate $candidate, EntityManagerInterface $em): RedirectResponse
    {
        $quizCandidate = $this->quizCandidateRepository->findOneBy([
            'quiz' => $quiz,
            'candidate' => $candidate,
        ]);

        if (!$quizCandidate instanceof QuizCandidate) {
            // Create new QuizCandidate if it doesn't exist (inactive by default when first toggling)
            $quizCandidate = new QuizCandidate($quiz, $candidate);
            $quizCandidate->active = false;
            $em->persist($quizCandidate);
        } else {
            $quizCandidate->active = !$quizCandidate->active;
        }

        $em->flush();

        $this->addFlash('success', $this->translator->trans('Candidate status updated'));

        return $this->redirectToRoute('tvdt_backoffice_quiz_candidates_tab', ['seasonCode' => $quiz->season->seasonCode, 'quiz' => $quiz->id]);
    }
}
