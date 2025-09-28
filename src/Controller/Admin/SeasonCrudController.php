<?php

declare(strict_types=1);

namespace Tvdt\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Tvdt\Entity\Season;

/** @extends AbstractCrudController<Season> */
class SeasonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Season::class;
    }
}
