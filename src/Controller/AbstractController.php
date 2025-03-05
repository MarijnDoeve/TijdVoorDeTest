<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\FlashType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractBaseController;

abstract class AbstractController extends AbstractBaseController
{
    protected function addFlash(FlashType|string $type, mixed $message): void
    {
        if ($type instanceof FlashType) {
            $type = $type->value;
        }
        parent::addFlash($type, $message);
    }
}
