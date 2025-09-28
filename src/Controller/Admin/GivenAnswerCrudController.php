<?php

declare(strict_types=1);

namespace Tvdt\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Tvdt\Entity\GivenAnswer;

/** @extends AbstractCrudController<GivenAnswer> */
class GivenAnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GivenAnswer::class;
    }
}
