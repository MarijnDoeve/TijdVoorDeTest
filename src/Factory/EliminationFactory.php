<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Elimination;
use App\Entity\Quiz;
use App\Repository\CandidateRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class EliminationFactory
{
    public function __construct(
        private CandidateRepository $candidateRepository,
        private EntityManagerInterface $em,
    ) {}

    public function createEliminationFromQuiz(Quiz $quiz): Elimination
    {
        $elimination = new Elimination($quiz);
        $this->em->persist($elimination);

        $scores = $this->candidateRepository->getScores($quiz);

        $simpleScores = [];

        foreach (array_reverse($scores) as $i => $score) {
            $simpleScores[$score['name']] = $i < $quiz->getDropouts() ? Elimination::SCREEN_RED : Elimination::SCREEN_GREEN;
        }

        $elimination->setData($simpleScores);

        $this->em->flush();

        return $elimination;
    }
}
