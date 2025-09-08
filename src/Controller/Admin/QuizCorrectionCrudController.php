<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\QuizCandidate;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/** @extends AbstractCrudController<QuizCandidate> */
class QuizCorrectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuizCandidate::class;
    }
}
