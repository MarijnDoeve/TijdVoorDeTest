<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\Elimination;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
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
                && (
                    $subject instanceof Season
                    || $subject instanceof Elimination
                    || $subject instanceof Quiz
                    || $subject instanceof Candidate
                    || $subject instanceof Answer
                    || $subject instanceof Question
                );
    }

    /** @param Season|Elimination|Quiz|Candidate|Answer|Question $subject */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        switch (true) {
            case $subject instanceof Answer:
                $season = $subject->getQuestion()->getQuiz()->getSeason();
                break;
            case $subject instanceof Elimination:
            case $subject instanceof Question:
                $season = $subject->getQuiz()->getSeason();
                break;
            case $subject instanceof Candidate:
            case $subject instanceof Quiz:
                $season = $subject->getSeason();
                break;
            case $subject instanceof Season:
                $season = $subject;
                break;
            default:
                return false;
        }

        return match ($attribute) {
            self::EDIT, self::DELETE, self::ELIMINATION => $season->isOwner($user),
            default => false,
        };
    }
}
