<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\GivenAnswer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Enum\FlashType;
use App\Form\EnterNameType;
use App\Form\SelectSeasonType;
use App\Helpers\Base64;
use App\Repository\AnswerRepository;
use App\Repository\CandidateRepository;
use App\Repository\GivenAnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizCandidateRepository;
use App\Repository\SeasonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class QuizController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    #[Route(path: '/', name: 'app_quiz_select_season', methods: ['GET', 'POST'])]
    public function selectSeason(Request $request, SeasonRepository $seasonRepository): Response
    {
        $form = $this->createForm(SelectSeasonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seasonCode = $form->get('season_code')->getData();

            if ([] === $seasonRepository->findBy(['seasonCode' => $seasonCode])) {
                $this->addFlash(FlashType::Warning, $this->translator->trans('Invalid season code'));

                return $this->redirectToRoute('app_quiz_select_season');
            }

            return $this->redirectToRoute('app_quiz_enter_name', ['seasonCode' => $seasonCode]);
        }

        return $this->render('quiz/select_season.html.twig', ['form' => $form]);
    }

    #[Route(path: '/{seasonCode:season}', name: 'app_quiz_enter_name', requirements: ['seasonCode' => self::SEASON_CODE_REGEX])]
    public function enterName(
        Request $request,
        Season $season,
    ): Response {
        $form = $this->createForm(EnterNameType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();

            return $this->redirectToRoute('app_quiz_quiz_page', ['seasonCode' => $season->getSeasonCode(), 'nameHash' => Base64::base64UrlEncode($name)]);
        }

        return $this->render('quiz/enter_name.twig', ['season' => $season, 'form' => $form]);
    }

    #[Route(
        path: '/{seasonCode:season}/{nameHash}',
        name: 'app_quiz_quiz_page',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'nameHash' => self::CANDIDATE_HASH_REGEX],
    )]
    public function quizPage(
        Season $season,
        string $nameHash,
        Request $request,
        CandidateRepository $candidateRepository,
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository,
        GivenAnswerRepository $givenAnswerRepository,
        QuizCandidateRepository $quizCandidateRepository,
    ): Response {
        $candidate = $candidateRepository->getCandidateByHash($season, $nameHash);

        if (!$candidate instanceof Candidate) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('Candidate not found'));

            return $this->redirectToRoute('app_quiz_enter_name', ['seasonCode' => $season->getSeasonCode()]);
        }

        $quiz = $season->getActiveQuiz();

        if (!$quiz instanceof Quiz) {
            $this->addFlash(FlashType::Warning, $this->translator->trans('There is no active quiz'));

            return $this->redirectToRoute('app_quiz_enter_name', ['seasonCode' => $season->getSeasonCode()]);
        }

        if ('POST' === $request->getMethod()) {
            $answer = $answerRepository->findOneBy(['id' => $request->request->get('answer')]);

            if (!$answer instanceof Answer) {
                throw new BadRequestHttpException('Invalid Answer ID');
            }

            $givenAnswer = new GivenAnswer($candidate, $answer->getQuestion()->getQuiz(), $answer);
            $givenAnswerRepository->save($givenAnswer);

            return $this->redirectToRoute('app_quiz_quiz_page', ['seasonCode' => $season->getSeasonCode(), 'nameHash' => $nameHash]);
        }

        $question = $questionRepository->findNextQuestionForCandidate($candidate);

        if (!$question instanceof Question) {
            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz completed'));

            return $this->redirectToRoute('app_quiz_enter_name', ['seasonCode' => $season->getSeasonCode()]);
        }

        $quizCandidateRepository->createIfNotExist($quiz, $candidate);

        return $this->render('quiz/question.twig', ['candidate' => $candidate, 'question' => $question, 'season' => $season]);
    }
}
