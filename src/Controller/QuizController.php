<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Season;
use App\Enum\FlashType;
use App\Form\EnterNameType;
use App\Form\SelectSeasonType;
use App\Helpers\Base64;
use App\Repository\CandidateRepository;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class QuizController extends AbstractController
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
        Season $season,
        string $nameHash,
        CandidateRepository $candidateRepository,
        QuestionRepository $questionRepository,
    ): Response {
        $candidate = $candidateRepository->getCandidateByHash($season, $nameHash);

        if (!$candidate instanceof Candidate) {
            // Add option to add new candidate when preregister is disabled
            $this->addFlash(FlashType::Danger->value, 'Candidate not found');

            return $this->redirectToRoute('enter_name', ['seasonCode' => $season->getSeasonCode()]);
        }

        $question = $questionRepository->findNextQuestionForCandidate($candidate);

        return $this->render('quiz/question.twig', ['candidate' => $candidate, 'question' => $question]);
    }
}
