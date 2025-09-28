<?php

declare(strict_types=1);

namespace Tvdt\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Tvdt\Entity\Answer;

/** @extends AbstractCrudController<Answer> */
class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }
}
