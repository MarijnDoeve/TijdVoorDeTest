<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Season;
use App\Helpers\Base64;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Safe\Exceptions\UrlException;

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
            $name = Base64::base64_url_decode($hash);
        } catch (UrlException) {
            return null;
        }

        return $this->createQueryBuilder('c')
            ->where('c.season = :season')
            ->andWhere('lower(c.name) = lower(:name)')
            ->setParameter('season', $season)
            ->setParameter('name', $name)
            ->getQuery()->getOneOrNullResult();
    }

    public function save(Candidate $candidate, bool $flush = true): void
    {
        $this->getEntityManager()->persist($candidate);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
