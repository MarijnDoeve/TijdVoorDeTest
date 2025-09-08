<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Answer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/** @extends AbstractCrudController<Answer> */
class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }
}
