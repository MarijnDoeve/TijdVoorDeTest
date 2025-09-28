<?php

declare(strict_types=1);

namespace Tvdt\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Tvdt\Entity\Question;

/** @extends AbstractCrudController<Question> */
class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }
}
