<?php

declare(strict_types=1);

namespace App\Controller\Backoffice;

use App\Entity\Elimination;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Factory\EliminationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrepareEliminationController extends AbstractController
{
    #[Route('/backoffice/elimination/{seasonCode}/{quiz}/prepare', name: 'app_prepare_elimination')]
    public function index(Season $season, Quiz $quiz, EliminationFactory $eliminationFactory): Response
    {
        $elimination = $eliminationFactory->createEliminationFromQuiz($quiz);

        return $this->redirectToRoute('app_prepare_elimination_view', ['elimination' => $elimination->getId()]);
    }

    #[Route('/backoffice/elimination/{elimination}', name: 'app_prepare_elimination_view')]
    public function viewElimination(Elimination $elimination, Request $request, EntityManagerInterface $em): Response
    {
        if ('POST' === $request->getMethod()) {
            $elimination->updateFromInputBag($request->request);
            $em->flush();

            if (true === $request->request->getBoolean('start')) {
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
