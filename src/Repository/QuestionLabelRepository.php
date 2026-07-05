<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\QuestionLabel;
use Tvdt\Entity\Season;

/** @extends ServiceEntityRepository<QuestionLabel> */
class QuestionLabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionLabel::class);
    }

    public function findBySlugAndSeason(string $slug, Season $season): ?QuestionLabel
    {
        return $this->findOneBy(['slug' => $slug, 'season' => $season]);
    }

    public function slugExistsForSeason(string $slug, Season $season, ?QuestionLabel $excluding = null): bool
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.slug = :slug')
            ->andWhere('l.season = :season')
            ->setParameter('slug', $slug)
            ->setParameter('season', $season);

        if ($excluding instanceof QuestionLabel) {
            $qb->andWhere('l.id != :id')->setParameter('id', $excluding->id);
        }

        return (int) $qb->select('COUNT(l.id)')->getQuery()->getSingleScalarResult() > 0;
    }
}
