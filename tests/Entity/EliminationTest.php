<?php

declare(strict_types=1);

namespace Tvdt\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Tvdt\Entity\Elimination;
use Tvdt\Entity\Quiz;

#[CoversClass(Elimination::class)]
final class EliminationTest extends TestCase
{
    public function testGetScreenColourReturnsNullForNullName(): void
    {
        $elimination = new Elimination(new Quiz());

        $this->assertNull($elimination->getScreenColour(null));
    }

    public function testGetScreenColourReturnsNullForUnknownName(): void
    {
        $elimination = new Elimination(new Quiz());
        $elimination->data = $this->colours(['Tom' => Elimination::SCREEN_GREEN]);

        $this->assertNull($elimination->getScreenColour('Claudia'));
    }

    public function testGetScreenColourReturnsColourForKnownName(): void
    {
        $elimination = new Elimination(new Quiz());
        $elimination->data = $this->colours(['Tom' => Elimination::SCREEN_GREEN, 'Claudia' => Elimination::SCREEN_RED]);

        $this->assertSame(Elimination::SCREEN_RED, $elimination->getScreenColour('Claudia'));
    }

    public function testUpdateFromInputBagUpdatesKnownColours(): void
    {
        $elimination = new Elimination(new Quiz());
        $elimination->data = $this->colours(['Tom' => Elimination::SCREEN_GREEN, 'Claudia' => Elimination::SCREEN_RED]);

        $elimination->updateFromInputBag($this->inputBag(['colour-tom' => Elimination::SCREEN_RED]));

        $this->assertSame(Elimination::SCREEN_RED, $elimination->data['Tom']);
        $this->assertSame(Elimination::SCREEN_RED, $elimination->data['Claudia']);
    }

    public function testUpdateFromInputBagIgnoresMissingInput(): void
    {
        $elimination = new Elimination(new Quiz());
        $elimination->data = $this->colours(['Tom' => Elimination::SCREEN_GREEN]);

        $elimination->updateFromInputBag($this->inputBag([]));

        $this->assertSame(Elimination::SCREEN_GREEN, $elimination->data['Tom']);
    }

    public function testUpdateFromInputBagReturnsSelf(): void
    {
        $elimination = new Elimination(new Quiz());

        $this->assertSame($elimination, $elimination->updateFromInputBag($this->inputBag([])));
    }

    /**
     * @param array<string, string> $colours
     *
     * @return array<string, string>
     */
    private function colours(array $colours): array
    {
        return $colours;
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return InputBag<bool|float|int|string>
     */
    private function inputBag(array $parameters): InputBag
    {
        /** @var InputBag<bool|float|int|string> $inputBag */
        $inputBag = new InputBag($parameters);

        return $inputBag;
    }
}
