<?php

declare(strict_types=1);

namespace Tvdt\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Elimination;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;

/** @extends Voter<string, Season|Elimination|Quiz|Candidate|Answer|Question> */
final class SeasonVoter extends Voter
{
    public const string EDIT = 'SEASON_EDIT';

    public const string ELIMINATION = 'SEASON_ELIMINATION';

    public const string DELETE = 'SEASON_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::DELETE, self::ELIMINATION], true)
                && (
                    $subject instanceof Answer
                    || $subject instanceof Candidate
                    || $subject instanceof Elimination
                    || $subject instanceof Season
                    || $subject instanceof Question
                    || $subject instanceof Quiz
                );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isAdmin) {
            return true;
        }

        $season = match (true) {
            $subject instanceof Answer => $subject->question->quiz->season,
            $subject instanceof Elimination,
            $subject instanceof Question => $subject->quiz->season,
            $subject instanceof Candidate,
            $subject instanceof Quiz => $subject->season,
            $subject instanceof Season => $subject,
        };

        return match ($attribute) {
            self::EDIT, self::DELETE, self::ELIMINATION => $season->isOwner($user),
            default => false,
        };
    }
}
