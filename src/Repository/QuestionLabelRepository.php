<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\QuestionLabel;

/** @extends ServiceEntityRepository<QuestionLabel> */
class QuestionLabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionLabel::class);
    }
}
