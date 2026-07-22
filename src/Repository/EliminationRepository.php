<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Tvdt\Entity\Elimination;

/** @extends ServiceEntityRepository<Elimination> */
class EliminationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Elimination::class);
    }

    /** Fetch an elimination with its screen views and each view's candidate eager-loaded, to avoid an N+1 when the log table renders. */
    public function fetchWithScreenViewCandidates(Uuid $id): Elimination
    {
        return $this->getEntityManager()->createQuery(<<<dql
            select e, sv, c from Tvdt\Entity\Elimination e
            left join e.screenViews sv
            left join sv.candidate c
            where e.id = :id
            dql)->setParameter('id', $id)->getSingleResult();
    }
}
