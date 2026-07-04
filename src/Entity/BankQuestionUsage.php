<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(fields: ['bankQuestion', 'quiz'])]
class BankQuestionUsage
{
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public private(set) \DateTimeImmutable $created;

    public function __construct(
        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'usages')]
        public private(set) BankQuestion $bankQuestion,

        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        #[ORM\ManyToOne]
        public private(set) Quiz $quiz,
    ) {}
}
