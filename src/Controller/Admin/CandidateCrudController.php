<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Candidate;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/** @extends AbstractCrudController<Candidate> */
class CandidateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Candidate::class;
    }
}
