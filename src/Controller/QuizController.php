<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\GivenAnswer;
use App\Entity\Question;
use App\Entity\Season;
use App\Enum\FlashType;
use App\Form\EnterNameType;
use App\Form\SelectSeasonType;
use App\Helpers\Base64;
use App\Repository\AnswerRepository;
use App\Repository\CandidateRepository;
use App\Repository\GivenAnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SeasonRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class QuizController extends AbstractController
{
    public const string SEASON_CODE_REGEX = '[A-Za-z\d]{5}';

    private const string CANDIDATE_HASH_REGEX = '[\w\-=]+';

    public function __construct(private readonly TranslatorInterface $translator) {}

    #[Route(path: '/', name: 'app_quiz_selectseason', methods: ['GET', 'POST'])]
    public function selectSeason(Request $request, SeasonRepository $seasonRepository): Response
    {
        $form = $this->createForm(SelectSeasonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seasonCode = $form->get('season_code')->getData();

            if ([] === $seasonRepository->findBy(['seasonCode' => $seasonCode])) {
                $this->addFlash(FlashType::Warning, $this->translator->trans('Invalid season code'));

                return $this->redirectToRoute('app_quiz_selectseason');
            }

            return $this->redirectToRoute('app_quiz_entername', ['seasonCode' => $seasonCode]);
        }

        return $this->render('quiz/select_season.html.twig', ['form' => $form]);
    }

    #[Route(path: '/{seasonCode}', name: 'app_quiz_entername', requirements: ['seasonCode' => self::SEASON_CODE_REGEX])]
    public function enterName(
        Request $request,
        #[MapEntity(mapping: ['seasonCode' => 'seasonCode'])]
        Season $season,
    ): Response {
        $form = $this->createForm(EnterNameType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();

            return $this->redirectToRoute('app_quiz_quizpage', ['seasonCode' => $season->getSeasonCode(), 'nameHash' => Base64::base64UrlEncode($name)]);
        }

        return $this->render('quiz/enter_name.twig', ['season' => $season, 'form' => $form]);
    }

    #[Route(
        path: '/{seasonCode}/{nameHash}',
        name: 'app_quiz_quizpage',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'nameHash' => self::CANDIDATE_HASH_REGEX],
    )]
    public function quizPage(
        #[MapEntity(mapping: ['seasonCode' => 'seasonCode'])]
        Season $season,
        string $nameHash,
        CandidateRepository $candidateRepository,
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository,
        GivenAnswerRepository $givenAnswerRepository,
        Request $request,
    ): Response {
        $candidate = $candidateRepository->getCandidateByHash($season, $nameHash);

        if (!$candidate instanceof Candidate) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('Candidate not found'));

            return $this->redirectToRoute('app_quiz_entername', ['seasonCode' => $season->getSeasonCode()]);
        }

        if ('POST' === $request->getMethod()) {
            $answer = $answerRepository->findOneBy(['id' => $request->request->get('answer')]);

            if (!$answer instanceof Answer) {
                throw new BadRequestException('Invalid Answer ID');
            }

            $givenAnswer = (new GivenAnswer())
                ->setCandidate($candidate)
                ->setAnswer($answer)
            ->setQuiz($answer->getQuestion()->getQuiz());
            $givenAnswerRepository->save($givenAnswer);
        }

        $question = $questionRepository->findNextQuestionForCandidate($candidate);

        if (!$question instanceof Question) {
            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz completed'));

            return $this->redirectToRoute('app_quiz_entername', ['seasonCode' => $season->getSeasonCode()]);
        }

        // TODO One first question record time
        return $this->render('quiz/question.twig', ['candidate' => $candidate, 'question' => $question]);
    }
}
