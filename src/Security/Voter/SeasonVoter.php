<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Season;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, Season> */
final class SeasonVoter extends Voter
{
    public const string EDIT = 'SEASON_EDIT';

    public const string ELIMINATION = 'SEASON_ELIMINATION';

    public const string DELETE = 'SEASON_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::DELETE, self::ELIMINATION], true)
            && $subject instanceof Season;
    }

    /** @param Season $subject */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return match ($attribute) {
            self::EDIT, self::DELETE, self::ELIMINATION => $subject->isOwner($user),
            default => false,
        };
    }
}
