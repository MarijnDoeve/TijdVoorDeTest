<?php

declare(strict_types=1);

namespace Tvdt\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Tvdt\Entity\User;

/** @extends AbstractCrudController<User> */
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}
