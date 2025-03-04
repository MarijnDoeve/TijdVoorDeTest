<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class KrtekFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $season = new Season();
        $manager->persist($season);

        $season->setName('Krtek Weekend')
            ->setSeasonCode('12345')
            ->setPreregisterCandidates(true)
            ->addCandidate(new Candidate('Claudia'))
            ->addCandidate(new Candidate('Eelco'))
            ->addCandidate(new Candidate('Elise'))
            ->addCandidate(new Candidate('Gert-Jan'))
            ->addCandidate(new Candidate('Iris'))
            ->addCandidate(new Candidate('Jari'))
            ->addCandidate(new Candidate('Lara'))
            ->addCandidate(new Candidate('Lotte'))
            ->addCandidate(new Candidate('Myrthe'))
            ->addCandidate(new Candidate('Philine'))
            ->addCandidate(new Candidate('Remy'))
            ->addCandidate(new Candidate('Robbert'))
            ->addCandidate(new Candidate('Tom'))
            ->addQuiz($this->createQuiz1($season));

        $manager->flush();
    }
    private function createQuiz1(Season $season): Quiz {
        return (new Quiz())
            ->setName('Quiz 1')
            ->setSeason($season)

            ->addQuestion((new Question())
                ->setQuestion('Is de Krtek een man of een vrouw?')
                ->addAnswer(new Answer('Ja', true))
                ->addAnswer(new Answer('Nee'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Hoeveel broers heeft de Krtek?')
                ->addAnswer(new Answer('Geen', true))
                ->addAnswer(new Answer('1'))
                ->addAnswer(new Answer('2'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Wat is de lievelingsfeestdag van de Krtek?')
                ->addAnswer(new Answer('Geen'))
                ->addAnswer(new Answer('Diens eigen verjaardag'))
                ->addAnswer(new Answer('Koningsdag'))
                ->addAnswer(new Answer('Kerst', true))
                ->addAnswer(new Answer('Oud en Nieuw'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Hoe kwam de Krtek naar Kersteren vandaag?')
                ->addAnswer(new Answer('Met het OV', true))
                ->addAnswer(new Answer('Met de auto'))
            )
            ->addQuestion((new Question())
                ->setQuestion('Met wie keek de Kretek video bij binnenkomst?')
                ->addAnswer(new Answer('Claudia'))
                ->addAnswer(new Answer('Eelco'))
                ->addAnswer(new Answer('Elise'))
                ->addAnswer(new Answer('Gert-Jan'))
                ->addAnswer(new Answer('Iris'))
                ->addAnswer(new Answer('Jari'))
                ->addAnswer(new Answer('Lara'))
                ->addAnswer(new Answer('Lotte'))
                ->addAnswer(new Answer('Myrthe'))
                ->addAnswer(new Answer('Philine'))
                ->addAnswer(new Answer('Remy'))
                ->addAnswer(new Answer('Robbert'))
                ->addAnswer(new Answer('Tom', true))
            )

            ->addQuestion((new Question())
                ->setQuestion('Welk advies zou de Krtek zichzelf als kind geven?')
                ->addAnswer(new Answer('Geef je vader een knuffel.'))
                ->addAnswer(new Answer('Trek je wat minder aan van anderen.'))
                ->addAnswer(new Answer('Luister meer naar je eigen gevoel in plaats van naar wat anderen vinden.'))
                ->addAnswer(new Answer('Stel niet alles tot het laatste moment uit.'))
                ->addAnswer(new Answer('Altijd doorgaan.'))
                ->addAnswer(new Answer('Probeer ook eens buiten de lijntjes te kleuren', true))
                ->addAnswer(new Answer('Ga als je groot bent op groepsreis! '))
                ->addAnswer(new Answer('Trek minder aan van de mening van anderen, het is oké om anders te zijn.'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Wat voor soort schoenen droeg de Krtek bij het diner?')
                ->addAnswer(new Answer('Sneakers'))
                ->addAnswer(new Answer('Wandel-/bergschoenen', true))
                ->addAnswer(new Answer('Lederen schoenen'))
                ->addAnswer(new Answer('Pantoffels'))
                ->addAnswer(new Answer('Hakken'))
                ->addAnswer(new Answer('Geen schoenen, alleen sokken'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Met welk vervoersmiddel reist de Krtek het liefste?')
                ->addAnswer(new Answer('Fiets', true))
                ->addAnswer(new Answer('Auto'))
                ->addAnswer(new Answer('Trein'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Heeft de Krtek een eigen auto?')
                ->addAnswer(new Answer('Ja'))
                ->addAnswer(new Answer('Nee', true))
            )

            ->addQuestion((new Question())
                ->setQuestion('Van wie is de quote die de Krtek gepakt heeft')
                ->addAnswer(new Answer('Karen'))
                ->addAnswer(new Answer('Gilles de Coster'))
                ->addAnswer(new Answer('Kees Tol'))
                ->addAnswer(new Answer('Harry en John'))
                ->addAnswer(new Answer('Georgina Verbaan'))
                ->addAnswer(new Answer('Marc-Marie Huijbregts'))
                ->addAnswer(new Answer('Fresia Cousiño Arias, Rik van de Westelaken'))
                ->addAnswer(new Answer('Ellie Lust'))
                ->addAnswer(new Answer('Bouba'))
                ->addAnswer(new Answer('Jan Versteegh'))
                ->addAnswer(new Answer('Dick Jol'))
                ->addAnswer(new Answer('Karin de Groot'))
                ->addAnswer(new Answer('Pieter'))
                ->addAnswer(new Answer('Renée Fokker'))
                ->addAnswer(new Answer('Sam, Davy', true))
            )

            ->addQuestion((new Question())
                ->setQuestion('Zou de Krtek molboekjes, jokers, vrijstellingen of topito’s uit iemands rugzak stelen om te kunnen winnen?')
                ->addAnswer(new Answer('Ja'))
                ->addAnswer(new Answer('Nee', true))
            )

            ->addQuestion((new Question())
                ->setQuestion('In wat voor bed slaapt de Krtek dit weekend?')
                ->addAnswer(new Answer('Éénpersoons, losstaand bed'))
                ->addAnswer(new Answer('Éénpersoonsbed, tegen een ander bed aan', true))
                ->addAnswer(new Answer('Tweepersoons bed'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Hoeveel jaar heeft de Krtek gedaan over de middelbare school?')
                ->addAnswer(new Answer('5'))
                ->addAnswer(new Answer('6', true))
                ->addAnswer(new Answer('7'))
                ->addAnswer(new Answer('8'))
            )

            ->addQuestion((new Question())
                ->setQuestion('Waar zat de Krtek aan tafel bij het diner?')
                ->addAnswer(new Answer('Met de rug naar de accommodatie'))
                ->addAnswer(new Answer('Met de rug naar de buitenmuur', true))
            )

            ->addQuestion((new Question())
                ->setQuestion('Wie is de Krtek?')
                ->addAnswer(new Answer('Claudia', true))
                ->addAnswer(new Answer('Eelco'))
                ->addAnswer(new Answer('Elise'))
                ->addAnswer(new Answer('Gert-Jan'))
                ->addAnswer(new Answer('Iris'))
                ->addAnswer(new Answer('Jari'))
                ->addAnswer(new Answer('Lara'))
                ->addAnswer(new Answer('Lotte'))
                ->addAnswer(new Answer('Myrthe'))
                ->addAnswer(new Answer('Philine'))
                ->addAnswer(new Answer('Remy'))
                ->addAnswer(new Answer('Robbert'))
                ->addAnswer(new Answer('Tom'))
            )
        ;
    }
}
