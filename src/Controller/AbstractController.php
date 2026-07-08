<?php

declare(strict_types=1);

namespace Tvdt\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractBaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;
use Tvdt\Enum\FlashType;

abstract class AbstractController extends AbstractBaseController
{
    protected const string SEASON_CODE_REGEX = '[A-Za-z\d]{5}';

    protected const string CANDIDATE_HASH_REGEX = '[\w\-=]+';

    protected User $authenticatedUser {
        get {
            $user = $this->getUser();
            \assert($user instanceof User);

            return $user;
        }
    }

    protected function assertSameSeason(Season $season, Season $subjectSeason): void
    {
        if ($season !== $subjectSeason) {
            throw new NotFoundHttpException();
        }
    }

    #[\Override]
    protected function addFlash(FlashType|string $type, mixed $message): void
    {
        if ($type instanceof FlashType) {
            $type = $type->value;
        }

        parent::addFlash($type, $message);
    }
}
