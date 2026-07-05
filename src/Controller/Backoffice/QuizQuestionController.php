<?php

declare(strict_types=1);

namespace Tvdt\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Controller\AbstractController;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Enum\FlashType;
use Tvdt\Form\QuestionFormType;
use Tvdt\Security\Voter\SeasonVoter;

#[AsController]
#[IsGranted('ROLE_USER')]
class QuizQuestionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {}

    #[IsGranted(SeasonVoter::MODIFY_QUIZ_CONTENT, subject: 'question')]
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/question/{question}/edit',
        name: 'tvdt_backoffice_quiz_question_edit',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID, 'question' => Requirement::UUID],
    )]
    public function edit(Season $season, Quiz $quiz, Question $question, Request $request): Response
    {
        if ($question->quiz !== $quiz || $quiz->season !== $season) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(QuestionFormType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyAnswerOrdering($question);
            $this->em->flush();

            $this->addFlash(FlashType::Success, $this->translator->trans('Question updated'));

            return $this->redirectToRoute('tvdt_backoffice_quiz_overview', [
                'seasonCode' => $season->seasonCode,
                'quiz' => $quiz->id,
            ]);
        }

        return $this->render('backoffice/quiz/question_form.html.twig', [
            'season' => $season,
            'quiz' => $quiz,
            'question' => $question,
            'form' => $form,
        ]);
    }

    private function applyAnswerOrdering(Question $question): void
    {
        $ordering = 1;
        foreach ($question->answers as $answer) {
            $answer->ordering = $ordering++;
        }
    }
}
