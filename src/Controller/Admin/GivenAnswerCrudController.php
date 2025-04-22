<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\GivenAnswer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class GivenAnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GivenAnswer::class;
    }
}
