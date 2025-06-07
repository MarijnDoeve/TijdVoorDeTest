<?php

declare(strict_types=1);

namespace App\Controller\Backoffice;

use App\Controller\AbstractController;
use App\Entity\Elimination;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Factory\EliminationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class PrepareEliminationController extends AbstractController
{
    #[Route(
        '/backoffice/season/{seasonCode:season}/quiz/{quiz}/elimination/prepare',
        name: 'app_prepare_elimination',
        requirements: ['seasonCode' => self::SEASON_CODE_REGEX, 'quiz' => Requirement::UUID],
    )]
    public function index(Season $season, Quiz $quiz, EliminationFactory $eliminationFactory): Response
    {
        $elimination = $eliminationFactory->createEliminationFromQuiz($quiz);

        return $this->redirectToRoute('app_prepare_elimination_view', ['elimination' => $elimination->getId()]);
    }

    #[Route(
        '/backoffice/elimination/{elimination}',
        name: 'app_prepare_elimination_view',
        requirements: ['elimination' => Requirement::UUID],
    )]
    public function viewElimination(Elimination $elimination, Request $request, EntityManagerInterface $em): Response
    {
        if ('POST' === $request->getMethod()) {
            $elimination->updateFromInputBag($request->request);
            $em->flush();

            if ($request->request->getBoolean('start')) {
                return $this->redirectToRoute('app_elimination', ['elimination' => $elimination->getId()]);
            }

            $this->addFlash('success', 'Elimination updated');
        }

        return $this->render('backoffice/prepare_elimination/index.html.twig', [
            'controller_name' => 'PrepareEliminationController',
            'elimination' => $elimination,
        ]);
    }
}
