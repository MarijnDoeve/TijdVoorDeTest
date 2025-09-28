<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;

/**
 * @extends ServiceEntityRepository<Season>
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    /** @return list<Season> Returns an array of Season objects */
    public function getSeasonsForUser(User $user): array
    {
        return $this->getEntityManager()->createQuery(<<<DQL
            select s from Tvdt\Entity\Season s where :user member of s.owners order by s.name
        DQL
        )->setParameter('user', $user)->getResult();
    }
}
