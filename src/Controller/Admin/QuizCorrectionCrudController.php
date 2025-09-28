<?php

declare(strict_types=1);

namespace Tvdt\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Tvdt\Entity\QuizCandidate;

/** @extends AbstractCrudController<QuizCandidate> */
class QuizCorrectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuizCandidate::class;
    }
}
