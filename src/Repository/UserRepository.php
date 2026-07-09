<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\Elimination;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;

/** @extends ServiceEntityRepository<User> */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /** Used to upgrade (rehash) the user's password automatically over time.
     * @param User $user
     * */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $user->password = $newHashedPassword;
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /** Deletes all outstanding reset-password tokens for the user (e.g. after a password or email change). */
    public function invalidateResetPasswordRequests(User $user): void
    {
        $this->getEntityManager()
            ->createQuery('delete from Tvdt\Entity\ResetPasswordRequest r where r.user = :user')
            ->setParameter('user', $user)
            ->execute();
    }

    /** Deletes the user, all seasons the user is the sole owner of, and the user's ownership of shared seasons. */
    public function deleteUser(User $user): void
    {
        $em = $this->getEntityManager();
        $em->wrapInTransaction(function () use ($em, $user): void {
            $this->invalidateResetPasswordRequests($user);

            $bankQuestionIds = [];
            foreach ($user->seasons->toArray() as $season) {
                if (1 === $season->owners->count()) {
                    $this->purgeSoftDeletableData($em, $season);
                    array_push($bankQuestionIds, ...$this->bankQuestionIds($season));
                    $em->remove($season);

                    continue;
                }

                $season->removeOwner($user);
            }

            $em->remove($user);
            $em->flush();

            // Gedmo\Loggable writes its own "removed" log entry as part of the flush above, so the
            // audit-log purge must happen after — purging first would just leave that final row behind.
            $this->purgeBankQuestionAuditLog($em, $bankQuestionIds);
        });
    }

    /**
     * QuizCandidate, GivenAnswer, and Elimination are Gedmo\SoftDeleteable, so cascading their
     * removal through the season/quiz/candidate relations only sets deletedAt — it never removes
     * the row. That leaves personal data behind indefinitely and, since Candidate/Answer are hard
     * deleted via orphanRemoval, it also breaks their foreign keys and rolls back the whole
     * deletion. Bulk DQL deletes bypass the Gedmo listener and physically remove these rows first.
     */
    private function purgeSoftDeletableData(EntityManagerInterface $em, Season $season): void
    {
        foreach ([QuizCandidate::class, GivenAnswer::class, Elimination::class] as $class) {
            $em->createQuery(<<<DQL
                delete from {$class} e
                where e.quiz in (select q from Tvdt\Entity\Quiz q where q.season = :season)
                DQL)
                ->setParameter('season', $season)
                ->execute();
        }
    }

    /** @return list<string> */
    private function bankQuestionIds(Season $season): array
    {
        return array_values(array_map(
            static fn (BankQuestion $bankQuestion): string => $bankQuestion->id->toString(),
            $season->bankQuestions->toArray(),
        ));
    }

    /**
     * Gedmo\Loggable audit rows (ext_log_entries) aren't foreign-keyed to the entity they log —
     * object_id is a plain string — so removing a BankQuestion never cleans up its history, and
     * the editor's username/email would otherwise remain in those rows forever.
     *
     * @param list<string> $bankQuestionIds
     */
    private function purgeBankQuestionAuditLog(EntityManagerInterface $em, array $bankQuestionIds): void
    {
        if ([] === $bankQuestionIds) {
            return;
        }

        $em->createQuery(<<<'DQL'
            delete from Tvdt\Entity\LogEntry l
            where l.objectClass = :class and l.objectId in (:ids)
            DQL)
            ->setParameter('class', BankQuestion::class)
            ->setParameter('ids', $bankQuestionIds)
            ->execute();
    }

    public function makeAdmin(string $email): void
    {
        $user = $this->findOneBy(['email' => $email]);
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User not found');
        }

        $user->roles = ['ROLE_ADMIN'];
        $this->getEntityManager()->flush();
    }
}
