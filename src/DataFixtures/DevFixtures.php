<?php

declare(strict_types=1);

namespace Tvdt\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Safe\DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tvdt\Entity\Answer;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Entity\Season;
use Tvdt\Entity\SeasonSettings;
use Tvdt\Entity\User;

final class DevFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function getDependencies(): array
    {
        return [KrtekFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        \assert($manager instanceof EntityManagerInterface);

        $user = new User();
        $user->email = 'admin@tijdvoordetest.nl';
        $user->password = $this->passwordHasher->hashPassword($user, '12345678');
        $user->roles = ['ROLE_ADMIN'];

        $manager->persist($user);

        $krtekOwner = new User();
        $krtekOwner->email = 'krtek@tijdvoordetest.nl';
        $krtekOwner->password = $this->passwordHasher->hashPassword($krtekOwner, '12345678');

        $manager->persist($krtekOwner);

        $this->setUpKrtekDemoState($manager, $krtekOwner);

        $manager->flush();
    }

    /**
     * Overlays a more realistic demo state on top of KrtekFixtures for local browsing: quiz 1 fully played out and
     * finalized, quiz 2 finalized and active, answer confirmation disabled. Kept out of KrtekFixtures itself since
     * that fixture is also loaded for the 'test' group, and a lot of tests rely on its original state (an
     * unstarted, assignable quiz to finalize/activate, an active quiz with no given answers yet, etc).
     */
    private function setUpKrtekDemoState(EntityManagerInterface $manager, User $owner): void
    {
        $season = $this->getReference(KrtekFixtures::KRTEK_SEASON, Season::class);
        $quiz1 = $this->getReference(KrtekFixtures::KRTEK_QUIZ_1, Quiz::class);
        $quiz2 = $this->getReference(KrtekFixtures::KRTEK_QUIZ_2, Quiz::class);

        $season->addOwner($owner);

        $this->fillInQuiz($manager, $quiz1);

        $quiz2->finalizedAt = new DateTimeImmutable();
        $season->activeQuiz = $quiz2;

        \assert($season->settings instanceof SeasonSettings);
        $season->settings->confirmAnswers = false;
    }

    /**
     * Has every candidate in the quiz's season fill in the quiz with a random answer to every question, spreading
     * the given answers' timestamps over a random 2-6 minute span per candidate so the results list (which is
     * ordered by score and then by time taken) shows realistic variation instead of every candidate finishing
     * instantly.
     */
    private function fillInQuiz(EntityManagerInterface $manager, Quiz $quiz): void
    {
        $givenAnswerMetadata = $manager->getClassMetadata(GivenAnswer::class);
        $questionCount = $quiz->questions->count();

        foreach ($quiz->season->candidates as $candidate) {
            $started = new DateTimeImmutable();
            $quizCandidate = new QuizCandidate($quiz, $candidate);
            $quizCandidate->started = $started;
            $manager->persist($quizCandidate);

            $durationSeconds = random_int(120, 360);

            foreach ($quiz->questions as $index => $question) {
                $answers = $question->answers;
                $randomAnswer = $answers->get(random_int(0, $answers->count() - 1));
                \assert($randomAnswer instanceof Answer);

                $givenAnswer = new GivenAnswer($candidate, $quiz, $randomAnswer);
                $manager->persist($givenAnswer);

                $offsetSeconds = (int) round($durationSeconds * ($index + 1) / $questionCount);
                $givenAnswerMetadata->setFieldValue($givenAnswer, 'created', $started->modify(\sprintf('+%d seconds', $offsetSeconds)));
            }
        }
    }
}
