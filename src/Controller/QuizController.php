<?php

declare(strict_types=1);

namespace Tvdt\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Enum\FlashType;
use Tvdt\Form\EnterNameType;
use Tvdt\Form\SelectSeasonType;
use Tvdt\Helpers\Base64;
use Tvdt\Repository\AnswerRepository;
use Tvdt\Repository\CandidateRepository;
use Tvdt\Repository\QuestionRepository;
use Tvdt\Repository\QuizCandidateRepository;
use Tvdt\Repository\SeasonRepository;

#[AsController]
final class QuizController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly EntityManagerInterface $entityManager, private readonly SeasonRepository $seasonRepository, private readonly CandidateRepository $candidateRepository, private readonly QuestionRepository $questionRepository, private readonly AnswerRepository $answerRepository, private readonly QuizCandidateRepository $quizCandidateRepository) {}

    #[Route(path: '/', name: 'tvdt_quiz_select_season', methods: ['GET', 'POST'])]
    public function selectSeason(Request $request): Response
    {
        $form = $this->createForm(SelectSeasonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seasonCode = $form->get('season_code')->getData();

            if ([] === $this->seasonRepository->findBy(['seasonCode' => $seasonCode])) {
                $this->addFlash(FlashType::Warning, $this->translator->trans('Invalid season code'));

                return $this->redirectToRoute('tvdt_quiz_select_season');
            }

            return $this->redirectToRoute('tvdt_quiz_enter_name', ['seasonCode' => $seasonCode]);
        }

        return $this->render('quiz/select_season.html.twig', ['form' => $form]);
    }

    #[Route(path: '/{seasonCode:season}', name: 'tvdt_quiz_enter_name', requirements: ['seasonCode' => self::SEASON_CODE_REGEX])]
    public function enterName(
        Request $request,
        Season $season,
    ): Response {
        $form = $this->createForm(EnterNameType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();

            return $this->redirectToRoute('tvdt_quiz_quiz_page', ['seasonCode' => $season->seasonCode, 'nameHash' => Base64::base64UrlEncode($name)]);
        }

        return $this->render('quiz/enter_name.twig', ['season' => $season, 'form' => $form]);
    }

    #[Route(
        path: '/{seasonCode:season}/{nameHash}',
        name: 'tvdt_quiz_quiz_page',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'nameHash' => self::CANDIDATE_HASH_REGEX],
    )]
    public function quizPage(
        Season $season,
        string $nameHash,
        Request $request,
    ): Response {
        $candidate = $this->candidateRepository->getCandidateByHash($season, $nameHash);

        if (!$candidate instanceof Candidate) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('Candidate not found'));

            return $this->redirectToRoute('tvdt_quiz_enter_name', ['seasonCode' => $season->seasonCode]);
        }

        $quiz = $season->activeQuiz;

        if (!$quiz instanceof Quiz) {
            $this->addFlash(FlashType::Warning, $this->translator->trans('There is no active quiz'));

            return $this->redirectToRoute('tvdt_quiz_enter_name', ['seasonCode' => $season->seasonCode]);
        }

        if ('POST' === $request->getMethod()) {
            // TODO: Extract saving answer logic to a service
            $answer = $this->answerRepository->findOneBy(['id' => $request->request->get('answer')]);

            if (!$answer instanceof Answer) {
                throw new BadRequestHttpException('Invalid Answer ID');
            }

            $givenAnswer = new GivenAnswer($candidate, $answer->question->quiz, $answer);
            $this->entityManager->persist($givenAnswer);
            $this->entityManager->flush();

            // end of extarcting saving answer logic
            return $this->redirectToRoute('tvdt_quiz_quiz_page', ['seasonCode' => $season->seasonCode, 'nameHash' => $nameHash]);
        }

        // TODO: Extract getting next question logic to a service
        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);

        // Keep creating flash here based on the return type of service call
        if (!$question instanceof Question) {
            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz completed'));

            return $this->redirectToRoute('tvdt_quiz_enter_name', ['seasonCode' => $season->seasonCode]);
        }

        $this->quizCandidateRepository->createIfNotExist($quiz, $candidate);

        // end of extracting getting next question logic
        return $this->render('quiz/question.twig', ['candidate' => $candidate, 'question' => $question, 'season' => $season]);
    }
}
