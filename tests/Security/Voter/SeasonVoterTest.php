<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\Elimination;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Entity\User;
use App\Security\Voter\SeasonVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SeasonVoterTest extends TestCase
{
    private SeasonVoter $seasonVoter;
    private TokenInterface&Stub $token;
    private User&Stub $user;

    protected function setUp(): void
    {
        $this->seasonVoter = new SeasonVoter();
        $this->token = $this->createStub(TokenInterface::class);

        $this->user = $this->createStub(User::class);
        $this->token->method('getUser')->willReturn($this->user);
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
        $quiz->method('getSeason')->willReturn($season);

        $elimination = self::createStub(Elimination::class);
        $elimination->method('getQuiz')->willReturn($quiz);

        $candidate = self::createStub(Candidate::class);
        $candidate->method('getSeason')->willReturn($season);

        $question = self::createStub(Question::class);
        $question->method('getQuiz')->willReturn($quiz);

        $answer = self::createStub(Answer::class);
        $answer->method('getQuestion')->willReturn($question);

        yield 'Season' => [$season];
        yield 'Elimination' => [$elimination];
        yield 'Quiz' => [$quiz];
        yield 'Candidate' => [$candidate];
        yield 'Question' => [$question];
        yield 'Answer' => [$answer];
    }
}
