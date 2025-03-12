<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\Correction;
use App\Entity\GivenAnswer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    #[\Override]
    public function index(): Response
    {
        //        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(SeasonCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TijdVoorDeTest');
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Season', 'fas fa-list', Season::class);
        yield MenuItem::linkToCrud('Quiz', 'fas fa-list', Quiz::class);
        yield MenuItem::linkToCrud('Question', 'fas fa-list', Question::class);
        yield MenuItem::linkToCrud('Candidate', 'fas fa-list', Candidate::class);
        yield MenuItem::linkToCrud('Correction', 'fas fa-list', Correction::class);
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('Given Answer', 'fas fa-list', GivenAnswer::class);
        yield MenuItem::linkToCrud('Answer', 'fas fa-list', Answer::class);
        yield MenuItem::linkToLogout('Logout', 'fa fa-exit');
    }
}
