<?php

declare(strict_types=1);

namespace Tvdt\Tests\Security\Voter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Elimination;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;
use Tvdt\Security\Voter\SeasonVoter;

#[CoversClass(SeasonVoter::class)]
final class SeasonVoterTest extends TestCase
{
    private SeasonVoter $seasonVoter;

    private TokenInterface&Stub $token;

    protected function setUp(): void
    {
        $this->seasonVoter = new SeasonVoter();
        $this->token = $this->createStub(TokenInterface::class);

        $user = $this->createStub(User::class);
        $this->token->method('getUser')->willReturn($user);
    }

    #[DataProvider('typesProvider')]
    public function testWithTypes(mixed $subject): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->seasonVoter->vote($this->token, $subject, ['SEASON_EDIT']));
    }

    public function testNotOwnerWillReturnDenied(): void
    {
        $season = self::createStub(Season::class);
        $season->method('isOwner')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->seasonVoter->vote($this->token, $season, ['SEASON_EDIT']));
    }

    public static function typesProvider(): \Generator
    {
        $season = self::createStub(Season::class);
        $season->method('isOwner')->willReturn(true);

        $quiz = self::createStub(Quiz::class);
        $quiz->season = $season;

        $elimination = self::createStub(Elimination::class);
        $elimination->quiz = $quiz;

        $candidate = self::createStub(Candidate::class);
        $candidate->season = $season;

        $question = self::createStub(Question::class);
        $question->quiz = $quiz;

        $answer = self::createStub(Answer::class);
        $answer->question = $question;

        yield 'Season' => [$season];
        yield 'Elimination' => [$elimination];
        yield 'Quiz' => [$quiz];
        yield 'Candidate' => [$candidate];
        yield 'Question' => [$question];
        yield 'Answer' => [$answer];
    }
}
