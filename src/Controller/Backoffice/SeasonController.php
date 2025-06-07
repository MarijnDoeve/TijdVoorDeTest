<?php

declare(strict_types=1);

namespace App\Controller\Backoffice;

use App\Controller\AbstractController;
use App\Entity\Candidate;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Enum\FlashType;
use App\Form\AddCandidatesFormType;
use App\Form\UploadQuizFormType;
use App\Security\Voter\SeasonVoter;
use App\Service\QuizSpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[IsGranted('ROLE_USER')]
class SeasonController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly EntityManagerInterface $em,
    ) {}

    #[Route(
        '/backoffice/season/{seasonCode:season}',
        name: 'app_backoffice_season',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function index(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
        ]);
    }

    #[Route(
        '/backoffice/season/{seasonCode:season}/add-candidate',
        name: 'app_backoffice_add_candidates',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function addCandidates(Season $season, Request $request): Response
    {
        $form = $this->createForm(AddCandidatesFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidates = $form->get('candidates')->getData();
            foreach (explode("\n", (string) $candidates) as $candidate) {
                $season->addCandidate(new Candidate($candidate));
            }

            $this->em->flush();

            return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
        }

        return $this->render('backoffice/season_add_candidates.html.twig', ['form' => $form]);
    }

    #[Route(
        '/backoffice/season/{seasonCode:season}/add-quiz',
        name: 'app_backoffice_quiz_add',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function addQuiz(Request $request, Season $season, QuizSpreadsheetService $quizSpreadsheet): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(UploadQuizFormType::class, $quiz);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var UploadedFile $sheet */
            $sheet = $form->get('sheet')->getData();

            $quizSpreadsheet->xlsxToQuiz($quiz, $sheet);

            $quiz->setSeason($season);
            $this->em->persist($quiz);
            $this->em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz Added!'));

            return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
        }

        return $this->render('/backoffice/quiz_add.html.twig', ['form' => $form, 'season' => $season]);
    }
}
