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
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class QuizController extends AbstractController
{
    public const string SEASON_CODE_REGEX = '[A-Za-z\d]{5}';
    private const string CANDIDATE_HASH_REGEX = '[\w\-=]+';

    #[Route(path: '/', name: 'select_season', methods: ['GET', 'POST'])]
    public function selectSeason(Request $request): Response
    {
        $form = $this->createForm(SelectSeasonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            return $this->redirectToRoute('enter_name', ['seasonCode' => $data['season_code']]);
        }

        return $this->render('quiz/select_season.html.twig', ['form' => $form]);
    }

    #[Route(path: '/{seasonCode}', name: 'enter_name', requirements: ['seasonCode' => self::SEASON_CODE_REGEX])]
    public function enterName(
        Request $request,
        #[MapEntity(mapping: ['seasonCode' => 'seasonCode'])]
        Season $season,
    ): Response {
        $form = $this->createForm(EnterNameType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $name = $data['name'];

            return $this->redirectToRoute('quiz_page', ['seasonCode' => $season->getSeasonCode(), 'nameHash' => Base64::base64_url_encode($name)]);
        }

        return $this->render('quiz/enter_name.twig', ['season' => $season, 'form' => $form]);
    }

    #[Route(
        path: '/{seasonCode}/{nameHash}',
        name: 'quiz_page',
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
            if (true === $season->isPreregisterCandidates()) {
                $this->addFlash(FlashType::Danger, 'Candidate not found');

                return $this->redirectToRoute('enter_name', ['seasonCode' => $season->getSeasonCode()]);
            }

            $candidate = new Candidate(Base64::base64_url_decode($nameHash));
            $candidateRepository->save($candidate);
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
            $this->addFlash(FlashType::Success, 'Quiz completed');

            return $this->redirectToRoute('enter_name', ['seasonCode' => $season->getSeasonCode()]);
        }

        // TODO One first question record time
        return $this->render('quiz/question.twig', ['candidate' => $candidate, 'question' => $question]);
    }
}
