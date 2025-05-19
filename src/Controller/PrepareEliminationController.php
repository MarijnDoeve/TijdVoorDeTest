<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrepareEliminationController extends AbstractController
{
    #[Route('/backoffice/elimination/{seasonCode}/{quiz}/prepare', name: 'app_prepare_elimination')]
    public function index(Season $season, Quiz $quiz): Response
    {
        return $this->render('prepare_elimination/index.html.twig', [
            'controller_name' => 'PrepareEliminationController',
            'season' => $season,
            'quiz' => $quiz,
        ]);
    }
}
