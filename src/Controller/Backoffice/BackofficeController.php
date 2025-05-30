<?php

declare(strict_types=1);

namespace App\Controller\Backoffice;

use App\Controller\AbstractController;
use App\Entity\Season;
use App\Entity\User;
use App\Form\CreateSeasonFormType;
use App\Repository\SeasonRepository;
use App\Service\QuizSpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted('ROLE_USER')]
final class BackofficeController extends AbstractController
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly Security $security,
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
    public function addSeason(Request $request, EntityManagerInterface $em): Response
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

    #[Route('/backoffice/template', name: 'app_backoffice_template', priority: 10)]
    public function getTemplate(QuizSpreadsheetService $excel): Response
    {
        $response = new StreamedResponse($excel->generateTemplate());
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="template.xlsx"');

        return $response;
    }
}
