<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/** @extends AbstractCrudController<Question> */
class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }
}
