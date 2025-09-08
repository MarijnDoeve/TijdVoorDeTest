<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Quiz;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/** @extends AbstractCrudController<Quiz> */
class QuizCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quiz::class;
    }
}
