<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Entity\User;
use App\Enum\FlashType;
use App\Form\AddCandidatesFormType;
use App\Form\CreateSeasonFormType;
use App\Form\UploadQuizFormType;
use App\Repository\CandidateRepository;
use App\Repository\SeasonRepository;
use App\Security\Voter\SeasonVoter;
use App\Service\QuizSpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[IsGranted('ROLE_USER')]
final class BackofficeController extends AbstractController
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly CandidateRepository $candidateRepository,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {}

    #[Route('/backoffice/', name: 'app_backoffice_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $seasons = $this->security->isGranted('ROLE_ADMIN')
            ? $this->seasonRepository->findAll()
            : $this->seasonRepository->getSeasonsForUser($user);

        return $this->render('backoffice/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }

    #[Route('/backoffice/add', name: 'app_backoffice_season_add', priority: 10)]
    public function seasonAdd(Request $request, EntityManagerInterface $em): Response
    {
        $season = new Season();
        $form = $this->createForm(CreateSeasonFormType::class, $season);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            \assert($user instanceof User);

            $season->addOwner($user);
            $season->generateSeasonCode();

            $em->persist($season);
            $em->flush();

            return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
        }

        return $this->render('backoffice/season_add.html.twig', ['form' => $form]);
    }

    #[Route('/backoffice/{seasonCode}', name: 'app_backoffice_season')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function season(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
        ]);
    }

    #[Route('/backoffice/{seasonCode}/{quiz}', name: 'app_backoffice_quiz')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function quiz(Season $season, Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'result' => $this->candidateRepository->getScores($quiz),
        ]);
    }

    #[Route('/backoffice/{seasonCode}/{quiz}/enable', name: 'app_backoffice_enable')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function enableQuiz(Season $season, ?Quiz $quiz, EntityManagerInterface $em): Response
    {
        $season->setActiveQuiz($quiz);
        $em->flush();

        return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
    }

    #[Route('/backoffice/{seasonCode}/add_candidate', name: 'app_backoffice_add_candidates', priority: 10)]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function addCandidates(Season $season, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AddCandidatesFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidates = $form->get('candidates')->getData();
            foreach (explode("\r\n", (string) $candidates) as $candidate) {
                $season->addCandidate(new Candidate($candidate));
            }

            $em->flush();

            return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
        }

        return $this->render('backoffice/season_add_candidates.html.twig', ['form' => $form]);
    }

    #[Route('/backoffice/{seasonCode}/add', name: 'app_backoffice_quiz_add', priority: 10)]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    public function addQuiz(Request $request, Season $season, QuizSpreadsheetService $quizSpreadsheet, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(UploadQuizFormType::class, $quiz);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var UploadedFile $sheet */
            $sheet = $form->get('sheet')->getData();

            $quizSpreadsheet->xlsxToQuiz($quiz, $sheet);

            $quiz->setSeason($season);
            $em->persist($quiz);
            $em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz Added!'));

            return $this->redirectToRoute('app_backoffice_season', ['seasonCode' => $season->getSeasonCode()]);
        }

        return $this->render('/backoffice/quiz_add.html.twig', ['form' => $form, 'season' => $season]);
    }

    #[Route('/backoffice/template', name: 'app_backoffice_template', priority: 10)]
    public function getTemplate(QuizSpreadsheetService $excel): Response
    {
        $response = new StreamedResponse($excel->generateTemplate());
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="template.xlsx"');

        return $response;
    }
}
