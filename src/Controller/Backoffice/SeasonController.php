<?php

declare(strict_types=1);

namespace Tvdt\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Controller\AbstractController;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Enum\FlashType;
use Tvdt\Form\AddCandidatesFormType;
use Tvdt\Form\SettingsForm;
use Tvdt\Form\UploadQuizFormType;
use Tvdt\Security\Voter\SeasonVoter;
use Tvdt\Service\QuizSpreadsheetService;

#[AsController]
#[IsGranted('ROLE_USER')]
class SeasonController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly QuizSpreadsheetService $quizSpreadsheet,
    ) {}

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}',
        name: 'tvdt_backoffice_season',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
    )]
    public function index(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
            'activeTab' => 'tests',
            'template' => 'backoffice/season/tab_tests.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/candidates',
        name: 'tvdt_backoffice_season_candidates',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function candidatesTab(Season $season): Response
    {
        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
            'activeTab' => 'candidates',
            'template' => 'backoffice/season/tab_candidates.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/settings',
        name: 'tvdt_backoffice_season_settings',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function settingsTab(Season $season, Request $request): Response
    {
        $form = $this->createForm(SettingsForm::class, $season->settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('tvdt_backoffice_season_settings', ['seasonCode' => $season->seasonCode]);
        }

        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
            'form' => $form,
            'activeTab' => 'settings',
            'template' => 'backoffice/season/tab_settings.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/add-candidate',
        name: 'tvdt_backoffice_add_candidates',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function addCandidates(Season $season, Request $request): Response
    {
        $form = $this->createForm(AddCandidatesFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidates = $form->get('candidates')->getData();
            foreach (explode("\n", (string) $candidates) as $candidate) {
                $season->addCandidate(new Candidate(mb_rtrim($candidate)));
            }

            $this->em->flush();

            return $this->redirectToRoute('tvdt_backoffice_season', ['seasonCode' => $season->seasonCode]);
        }

        return $this->render('backoffice/season_add_candidates.html.twig', ['form' => $form]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/add-quiz',
        name: 'tvdt_backoffice_quiz_add',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function addQuiz(Request $request, Season $season): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(UploadQuizFormType::class, $quiz);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var UploadedFile $sheet */
            $sheet = $form->get('sheet')->getData();

            $this->quizSpreadsheet->xlsxToQuiz($quiz, $sheet);

            $quiz->season = $season;
            $this->em->persist($quiz);
            $this->em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz Added!'));

            return $this->redirectToRoute('tvdt_backoffice_season', ['seasonCode' => $season->seasonCode]);
        }

        return $this->render('/backoffice/quiz_add.html.twig', ['form' => $form, 'season' => $season]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/add-blank-quiz',
        name: 'tvdt_backoffice_quiz_add_blank',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function addBlankQuiz(Request $request, Season $season): Response
    {
        $form = $this->createFormBuilder(new Quiz())
            ->add('name', TextType::class, ['label' => $this->translator->trans('Quiz name'), 'translation_domain' => false])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Quiz $quiz */
            $quiz = $form->getData();
            $quiz->season = $season;
            $this->em->persist($quiz);
            $this->em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Quiz Added!'));

            return $this->redirectToRoute('tvdt_backoffice_quiz_overview', [
                'seasonCode' => $season->seasonCode,
                'quiz' => $quiz->id,
            ]);
        }

        return $this->render('/backoffice/quiz_add_blank.html.twig', ['form' => $form, 'season' => $season]);
    }
}
