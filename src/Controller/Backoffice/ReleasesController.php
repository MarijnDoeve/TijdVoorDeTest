<?php

declare(strict_types=1);

namespace Tvdt\Controller\Backoffice;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tvdt\Controller\AbstractController;
use Tvdt\Service\GitHubReleasesService;

final class ReleasesController extends AbstractController
{
    public function __construct(
        private readonly GitHubReleasesService $gitHubReleasesService,
    ) {}

    #[Route('/backoffice/releases', name: 'tvdt_backoffice_releases', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('backoffice/releases/_frame.html.twig', [
            'releases' => $this->gitHubReleasesService->getReleases(),
        ]);
    }
}
