<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrepareEliminationController extends AbstractController
{
    #[Route('/backoffice/elimination/prepare', name: 'app_prepare_elimination')]
    public function index(): Response
    {
        return $this->render('prepare_elimination/index.html.twig', [
            'controller_name' => 'PrepareEliminationController',
        ]);
    }
}
