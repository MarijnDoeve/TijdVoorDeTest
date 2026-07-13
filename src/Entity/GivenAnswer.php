<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\GivenAnswerRepository;

#[Gedmo\SoftDeleteable]
#[ORM\Entity(repositoryClass: GivenAnswerRepository::class)]
#[ORM\UniqueConstraint(columns: ['candidate_id', 'question_id'], options: ['where' => '(deleted_at IS NULL)'])]
class GivenAnswer
{
    use SoftDeleteableEntity;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public private(set) \DateTimeImmutable $created;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne]
    public private(set) Question $question;

    public function __construct(
        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        public private(set) Candidate $candidate,

        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        #[ORM\ManyToOne]
        public private(set) Quiz $quiz,

        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        public private(set) Answer $answer,
    ) {
        $this->question = $answer->question;
    }
}
