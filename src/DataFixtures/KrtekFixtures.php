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
            ->setSeasonCode('krtek')
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
            ->addCandidate(new Candidate('Tom'));
        $quiz1 = $this->createQuiz1($season);
        $season->addQuiz($quiz1)
            ->setActiveQuiz($quiz1)
            ->addQuiz($this->createQuiz2($season));

        $manager->flush();
    }

    private function createQuiz1(Season $season): Quiz
    {
        return new Quiz()
            ->setName('Quiz 1')
            ->setSeason($season)

            ->addQuestion(new Question()
                ->setQuestion('Is de Krtek een man of een vrouw?')
                ->addAnswer(new Answer('Vrouw', true))
                ->addAnswer(new Answer('Man'))
                ->setOrdering(1),
            )

            ->addQuestion(new Question()
                ->setQuestion('Hoeveel broers heeft de Krtek?')
                ->addAnswer(new Answer('Geen', true))
                ->addAnswer(new Answer('1'))
                ->addAnswer(new Answer('2'))
                ->setOrdering(2),
            )

            ->addQuestion(new Question()
                ->setQuestion('Wat is de lievelingsfeestdag van de Krtek?')
                ->addAnswer(new Answer('Geen'))
                ->addAnswer(new Answer('Diens eigen verjaardag'))
                ->addAnswer(new Answer('Koningsdag'))
                ->addAnswer(new Answer('Kerst', true))
                ->addAnswer(new Answer('Oud en Nieuw'))
                ->setOrdering(3),
            )

            ->addQuestion(new Question()
                ->setQuestion('Hoe kwam de Krtek naar Kersteren vandaag?')
                ->addAnswer(new Answer('Met het OV', true))
                ->addAnswer(new Answer('Met de auto'))
                ->setOrdering(4),
            )
            ->addQuestion(new Question()
                ->setQuestion('Met wie keek de Krtek video bij binnenkomst?')
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
                ->setOrdering(5),
            )

            ->addQuestion(new Question()
                ->setQuestion('Welk advies zou de Krtek zichzelf als kind geven?')
                ->addAnswer(new Answer('Geef je vader een knuffel.'))
                ->addAnswer(new Answer('Trek je wat minder aan van anderen.'))
                ->addAnswer(new Answer('Luister meer naar je eigen gevoel in plaats van naar wat anderen vinden.'))
                ->addAnswer(new Answer('Stel niet alles tot het laatste moment uit.'))
                ->addAnswer(new Answer('Altijd doorgaan.'))
                ->addAnswer(new Answer('Probeer ook eens buiten de lijntjes te kleuren', true))
                ->addAnswer(new Answer('Ga als je groot bent op groepsreis! '))
                ->addAnswer(new Answer('Trek minder aan van de mening van anderen, het is oké om anders te zijn.'))
                ->setOrdering(6),
            )

            ->addQuestion(new Question()
                ->setQuestion('Wat voor soort schoenen droeg de Krtek bij het diner?')
                ->addAnswer(new Answer('Sneakers'))
                ->addAnswer(new Answer('Wandel-/bergschoenen', true))
                ->addAnswer(new Answer('Lederen schoenen'))
                ->addAnswer(new Answer('Pantoffels'))
                ->addAnswer(new Answer('Hakken'))
                ->addAnswer(new Answer('Geen schoenen, alleen sokken'))
                ->setOrdering(7),
            )

            ->addQuestion(new Question()
                ->setQuestion('Met welk vervoersmiddel reist de Krtek het liefste?')
                ->addAnswer(new Answer('Fiets', true))
                ->addAnswer(new Answer('Auto'))
                ->addAnswer(new Answer('Trein'))
                ->setOrdering(8),
            )

            ->addQuestion(new Question()
                ->setQuestion('Heeft de Krtek een eigen auto?')
                ->addAnswer(new Answer('Ja'))
                ->addAnswer(new Answer('Nee', true))
                ->setOrdering(9),
            )

            ->addQuestion(new Question()
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
                ->setOrdering(10),
            )

            ->addQuestion(new Question()
                ->setQuestion('Zou de Krtek molboekjes, jokers, vrijstellingen of topito’s uit iemands rugzak stelen om te kunnen winnen?')
                ->addAnswer(new Answer('Ja'))
                ->addAnswer(new Answer('Nee', true))
                ->setOrdering(11),
            )

            ->addQuestion(new Question()
                ->setQuestion('In wat voor bed slaapt de Krtek dit weekend?')
                ->addAnswer(new Answer('Éénpersoons, losstaand bed'))
                ->addAnswer(new Answer('Éénpersoonsbed, tegen een ander bed aan', true))
                ->addAnswer(new Answer('Tweepersoons bed'))
                ->setOrdering(12),
            )

            ->addQuestion(new Question()
                ->setQuestion('Hoeveel jaar heeft de Krtek gedaan over de middelbare school?')
                ->addAnswer(new Answer('5'))
                ->addAnswer(new Answer('6', true))
                ->addAnswer(new Answer('7'))
                ->addAnswer(new Answer('8'))
                ->setOrdering(13),
            )

            ->addQuestion(new Question()
                ->setQuestion('Waar zat de Krtek aan tafel bij het diner?')
                ->addAnswer(new Answer('Met de rug naar de accommodatie'))
                ->addAnswer(new Answer('Met de rug naar de buitenmuur', true))
                ->setOrdering(14),
            )

            ->addQuestion(new Question()
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
                ->setOrdering(15),
            )
        ;
    }

    private function createQuiz2(Season $season): Quiz
    {
        return new Quiz()
            ->setName('Quiz 2')
            ->setSeason($season)

            ->addQuestion(new Question()
                ->setQuestion('Is de Krtek een man of een vrouw?')
                ->addAnswer(new Answer('Man'))
                ->addAnswer(new Answer('Vrouw', true))
                ->setOrdering(1),
            )

            ->addQuestion(new Question()
                ->setQuestion('Heeft de Krtek dieetwensen of allergieën?')
                ->addAnswer(new Answer('nee'))
                ->addAnswer(new Answer('De Krtek is vegetariër', true))
                ->addAnswer(new Answer('De Krtek is flexitariër'))
                ->addAnswer(new Answer('De Krtek heeft een allergie'))
                ->addAnswer(new Answer('De Krtek heeft een intolerantie'))
                ->addAnswer(new Answer('De Krtek eet geen rundvlees'))
                ->addAnswer(new Answer('De Krtek eet geen waterdieren'))
                ->setOrdering(2),
            )

            ->addQuestion(new Question()
                ->setQuestion('Hoe heet het huisdier/de huisdieren van de Krtek?')
                ->addAnswer(new Answer('Amy, Karel en Floyd'))
                ->addAnswer(new Answer('Flip en Majoor'))
                ->addAnswer(new Answer('Benji'))
                ->addAnswer(new Answer('Sini'))
                ->addAnswer(new Answer('Tom'))
                ->addAnswer(new Answer('De huisdieren van de Krtek hebben geen naam'))
                ->addAnswer(new Answer('De Krtek heeft geen huisdieren', true))
                ->setOrdering(3),
            )

            ->addQuestion(new Question()
                ->setQuestion('Wat dronk de Krtek deze ochtend bij het ontbijt?')
                ->addAnswer(new Answer('Koffie'))
                ->addAnswer(new Answer('Thee'))
                ->addAnswer(new Answer('Water', true))
                ->addAnswer(new Answer('Melk'))
                ->addAnswer(new Answer('Sap'))
                ->addAnswer(new Answer('Niks'))
                ->setOrdering(4),
            )

            ->addQuestion(new Question()
                ->setQuestion('Waar ging de eerste vakantie die de Krtek zich nog herinnert heen?')
                ->addAnswer(new Answer('Denemarken'))
                ->addAnswer(new Answer('Drenthe'))
                ->addAnswer(new Answer('Mallorca'))
                ->addAnswer(new Answer('Marokko'))
                ->addAnswer(new Answer('Oostenrijk'))
                ->addAnswer(new Answer('Turkije'))
                ->addAnswer(new Answer('Zweden', true))
                ->setOrdering(5),
            )

            ->addQuestion(new Question()
                ->setQuestion('Met welk groepje ging de Krtek als eerste het Douanespel in?')
                ->addAnswer(new Answer('Het eerste groepje', true))
                ->addAnswer(new Answer('Het tweede groepje'))
                ->addAnswer(new Answer('Het derde groepje'))
                ->addAnswer(new Answer('Het vierde groepje'))
                ->addAnswer(new Answer('Het vijfde groepje'))
                ->setOrdering(6),
            )

            ->addQuestion(new Question()
                ->setQuestion('Gelooft de Krtek ergens in?')
                ->addAnswer(new Answer('Nee'))
                ->addAnswer(new Answer('Het universum', true))
                ->addAnswer(new Answer('Toeval'))
                ->addAnswer(new Answer('De Krtek is hindoeïstisch'))
                ->setOrdering(7),
            )

            ->addQuestion(new Question()
                ->setQuestion('At de Krtek op vrijdagavond heksenkaas tijdens het diner?')
                ->addAnswer(new Answer('Ja', true))
                ->addAnswer(new Answer('Nee'))
                ->setOrdering(8),
            )

            ->addQuestion(new Question()
                ->setQuestion('Hoe laat ging de Krtek gisteravond naar bed?')
                ->addAnswer(new Answer('Tussen 0:00 en 0:59 uur'))
                ->addAnswer(new Answer('Tussen 1:00 en 1:59 uur', true))
                ->addAnswer(new Answer('Tussen 2:00 en 2:59 uur'))
                ->addAnswer(new Answer('Na 3:00'))
                ->setOrdering(9),
            )

            ->addQuestion(new Question()
                ->setQuestion('Hoeveel batterijen heeft de Krtek naar het bord gebracht bij het douanespel?')
                ->addAnswer(new Answer('1'))
                ->addAnswer(new Answer('2'))
                ->addAnswer(new Answer('3'))
                ->addAnswer(new Answer('geen', true))
                ->setOrdering(10),
            )

            ->addQuestion(new Question()
                ->setQuestion('Wat keek de Krtek als kind graag op TV?')
                ->addAnswer(new Answer('Digimon', true))
                ->addAnswer(new Answer('Floris'))
                ->addAnswer(new Answer('Het huis Anubis'))
                ->addAnswer(new Answer('Sesamstraat'))
                ->addAnswer(new Answer('Spongebob Squarepants'))
                ->addAnswer(new Answer('Teletubbies'))
                ->setOrdering(11),
            )

            ->addQuestion(new Question()
                ->setQuestion('Waarin zat op de heenreis de bagage van de Krtek (voornamelijk)?')
                ->addAnswer(new Answer('In koffer(s)', true))
                ->addAnswer(new Answer('In losse tas(sen)'))
                ->addAnswer(new Answer('In een rugzak'))
                ->setOrdering(12),
            )

            ->addQuestion(new Question()
                ->setQuestion('Van welk geluid gaan de haren van de Krtek overeind staan?')
                ->addAnswer(new Answer('Een vork die door een metalen pan krast '))
                ->addAnswer(new Answer('Smakkende mensen'))
                ->addAnswer(new Answer('Een vork die over een bord schraapt'))
                ->addAnswer(new Answer('Schuren met schuurpapier'))
                ->addAnswer(new Answer('Nagels op een krijtbord'))
                ->addAnswer(new Answer('Servies dat tegen elkaar klettert'))
                ->addAnswer(new Answer('Het geroekoe van een duif', true))
                ->addAnswer(new Answer('Piepschuim'))
                ->setOrdering(13),
            )

            ->addQuestion(new Question()
                ->setQuestion('Wilde de Krtek penningmeester worden?')
                ->addAnswer(new Answer('Ja'))
                ->addAnswer(new Answer('Nee', true))
                ->setOrdering(14),
            )

            ->addQuestion(new Question()
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
                ->setOrdering(15),
            )
        ;
    }
}
