<?php

declare(strict_types=1);

namespace Tvdt\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Controller\AbstractController;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\QuestionLabel;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Enum\FlashType;
use Tvdt\Exception\BankQuestionAlreadyUsedException;
use Tvdt\Exception\QuizLockedException;
use Tvdt\Form\BankQuestionFormType;
use Tvdt\Repository\BankQuestionRepository;
use Tvdt\Repository\QuizRepository;
use Tvdt\Security\Voter\SeasonVoter;
use Tvdt\Service\QuestionBankService;

#[AsController]
#[IsGranted('ROLE_USER')]
class QuestionBankController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly BankQuestionRepository $bankQuestionRepository,
        private readonly QuizRepository $quizRepository,
        private readonly QuestionBankService $questionBankService,
    ) {}

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank',
        name: 'tvdt_backoffice_question_bank',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function index(Season $season, Request $request): Response
    {
        $label = null;
        $labelId = $request->query->getString('label');
        if ('' !== $labelId && Uuid::isValid($labelId)) {
            $label = $this->em->getRepository(QuestionLabel::class)->find($labelId);
            if ($label instanceof QuestionLabel && $label->season !== $season) {
                $label = null;
            }
        }

        return $this->render('backoffice/season.html.twig', [
            'season' => $season,
            'bankQuestions' => $this->bankQuestionRepository->findBySeason($season, $label),
            'assignableQuizzes' => $this->quizRepository->findAssignableForSeason($season),
            'activeLabel' => $label,
            'activeTab' => 'question-bank',
            'template' => 'backoffice/season/tab_question_bank.html.twig',
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank/new',
        name: 'tvdt_backoffice_question_bank_new',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        priority: 10,
    )]
    public function new(Season $season, Request $request): Response
    {
        $bankQuestion = new BankQuestion();

        $form = $this->createForm(BankQuestionFormType::class, $bankQuestion, ['season' => $season]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyAnswerOrdering($bankQuestion);
            $season->addBankQuestion($bankQuestion);
            $this->em->persist($bankQuestion);
            $this->em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Question added to the question bank'));

            return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
        }

        return $this->render('backoffice/question_bank/form.html.twig', [
            'season' => $season,
            'form' => $form,
            'bankQuestion' => null,
        ]);
    }

    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank/{bankQuestion}/edit',
        name: 'tvdt_backoffice_question_bank_edit',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'bankQuestion' => Requirement::UUID],
        priority: 10,
    )]
    public function edit(Season $season, BankQuestion $bankQuestion, Request $request): Response
    {
        $this->assertSameSeason($season, $bankQuestion->season);

        $form = $this->createForm(BankQuestionFormType::class, $bankQuestion, ['season' => $season]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyAnswerOrdering($bankQuestion);
            $this->em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Question updated'));

            return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
        }

        return $this->render('backoffice/question_bank/form.html.twig', [
            'season' => $season,
            'form' => $form,
            'bankQuestion' => $bankQuestion,
        ]);
    }

    #[IsCsrfTokenValid('delete_bank_question')]
    #[IsGranted(SeasonVoter::DELETE, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank/{bankQuestion}/delete',
        name: 'tvdt_backoffice_question_bank_delete',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'bankQuestion' => Requirement::UUID],
        methods: ['POST'],
        priority: 10,
    )]
    public function delete(Season $season, BankQuestion $bankQuestion): RedirectResponse
    {
        $this->assertSameSeason($season, $bankQuestion->season);

        $this->em->remove($bankQuestion);
        $this->em->flush();

        $this->addFlash(FlashType::Success, $this->translator->trans('Question removed from the question bank'));

        return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
    }

    #[IsCsrfTokenValid('assign_bank_question')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank/{bankQuestion}/assign',
        name: 'tvdt_backoffice_question_bank_assign',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'bankQuestion' => Requirement::UUID],
        methods: ['POST'],
        priority: 10,
    )]
    public function assign(Season $season, BankQuestion $bankQuestion, Request $request): RedirectResponse
    {
        $this->assertSameSeason($season, $bankQuestion->season);

        $quizId = $request->request->getString('quiz');
        if (!Uuid::isValid($quizId)) {
            throw new BadRequestHttpException('Invalid quiz');
        }

        $quiz = $this->em->getRepository(Quiz::class)->find($quizId);
        if (!$quiz instanceof Quiz || $quiz->season !== $season) {
            throw new BadRequestHttpException('Invalid quiz');
        }

        $this->denyAccessUnlessGranted(SeasonVoter::MODIFY_QUIZ_CONTENT, $quiz);

        try {
            $this->questionBankService->assignToQuiz($bankQuestion, $quiz);
            $this->addFlash(FlashType::Success, $this->translator->trans('Question added to quiz %quiz%', ['%quiz%' => $quiz->name]));
        } catch (QuizLockedException) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('This quiz can no longer be altered'));
        } catch (BankQuestionAlreadyUsedException) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('This question has already been used'));
        }

        return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
    }

    #[IsCsrfTokenValid('add_question_label')]
    #[IsGranted(SeasonVoter::EDIT, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank/labels',
        name: 'tvdt_backoffice_question_bank_labels',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX],
        methods: ['POST'],
        priority: 15,
    )]
    public function addLabel(Season $season, Request $request): RedirectResponse
    {
        $name = mb_trim($request->request->getString('name'));

        if ('' === $name || mb_strlen($name) > 64) {
            $this->addFlash(FlashType::Danger, $this->translator->trans('Invalid label name'));

            return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
        }

        $exists = $season->questionLabels->exists(static fn (int $key, QuestionLabel $label): bool => $label->name === $name);
        if (!$exists) {
            $season->addQuestionLabel(new QuestionLabel($name));
            $this->em->flush();
            $this->addFlash(FlashType::Success, $this->translator->trans('Label added'));
        }

        return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
    }

    #[IsCsrfTokenValid('delete_question_label')]
    #[IsGranted(SeasonVoter::DELETE, subject: 'season')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/question-bank/labels/{label}/delete',
        name: 'tvdt_backoffice_question_bank_label_delete',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'label' => Requirement::UUID],
        methods: ['POST'],
        priority: 15,
    )]
    public function deleteLabel(Season $season, QuestionLabel $label): RedirectResponse
    {
        $this->assertSameSeason($season, $label->season);

        foreach ($label->bankQuestions as $bankQuestion) {
            $bankQuestion->removeLabel($label);
        }

        $this->em->remove($label);
        $this->em->flush();

        $this->addFlash(FlashType::Success, $this->translator->trans('Label removed'));

        return $this->redirectToRoute('tvdt_backoffice_question_bank', ['seasonCode' => $season->seasonCode]);
    }

    private function assertSameSeason(Season $season, Season $subjectSeason): void
    {
        if ($season !== $subjectSeason) {
            throw new NotFoundHttpException();
        }
    }

    private function applyAnswerOrdering(BankQuestion $bankQuestion): void
    {
        $ordering = 1;
        foreach ($bankQuestion->answers as $answer) {
            $answer->ordering = $ordering++;
        }
    }
}
