<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\SeasonRepository;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    private const string SEASON_CODE_CHARACTERS = 'bcdfghjklmnpqrstvwxz';

    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column(length: 64)]
    public string $name;

    #[ORM\Column(length: 5)]
    public string $seasonCode;

    /** @var Collection<int, Quiz> */
    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'season', cascade: ['persist'], orphanRemoval: true)]
    public private(set) Collection $quizzes;

    /** @var Collection<int, Candidate> */
    #[ORM\OneToMany(targetEntity: Candidate::class, mappedBy: 'season', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    public private(set) Collection $candidates;

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'seasons')]
    public private(set) Collection $owners;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Quiz $activeQuiz = null;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    public ?SeasonSettings $settings = null;

    public function __construct()
    {
        $this->settings = new SeasonSettings();
        $this->quizzes = new ArrayCollection();
        $this->candidates = new ArrayCollection();
        $this->owners = new ArrayCollection();
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->season = $this;
        }

        return $this;
    }

    public function addCandidate(Candidate $candidate): static
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates->add($candidate);
            $candidate->season = $this;
        }

        return $this;
    }

    public function addOwner(User $owner): static
    {
        if (!$this->owners->contains($owner)) {
            $this->owners->add($owner);
        }

        return $this;
    }

    public function removeOwner(User $owner): static
    {
        $this->owners->removeElement($owner);

        return $this;
    }

    public function isOwner(User $user): bool
    {
        return $this->owners->contains($user);
    }

    public function generateSeasonCode(): self
    {
        $code = '';
        $len = mb_strlen(self::SEASON_CODE_CHARACTERS) - 1;

        for ($i = 0; $i < 5; ++$i) {
            $code .= self::SEASON_CODE_CHARACTERS[random_int(0, $len)];
        }

        $this->seasonCode = $code;

        return $this;
    }
}
