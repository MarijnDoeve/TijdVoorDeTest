<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Safe\Exceptions\UrlException;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Season;
use Tvdt\Helpers\Base64;

/**
 * @extends ServiceEntityRepository<Candidate>
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function getCandidateByHash(Season $season, string $hash): ?Candidate
    {
        try {
            $name = Base64::base64UrlDecode($hash);
        } catch (UrlException) {
            return null;
        }

        return $this->getEntityManager()->createQuery(<<<DQL
            select c from Tvdt\Entity\Candidate c
                where c.season = :season
                and lower(c.name) = lower(:name)
            DQL
        )->setParameter('season', $season)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }
}
