<?php

declare(strict_types=1);

namespace Tvdt\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;

final class KrtekFixtures extends Fixture implements FixtureGroupInterface
{
    public const string KRTEK_SEASON = 'krtek-seaspm';

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $season = new Season();
        $manager->persist($season);

        $season->name = 'Krtek Weekend';
        $season->seasonCode = 'krtek';
        $season
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
        $season->addQuiz($quiz1);
        $season->activeQuiz = $quiz1;
        $season->addQuiz($this->createQuiz2($season));

        $manager->flush();

        $this->addReference(self::KRTEK_SEASON, $season);
    }

    private function createQuiz1(Season $season): Quiz
    {
        $quiz = new Quiz();
        $quiz->name = 'Quiz 1';
        $quiz->season = $season;

        $q = new Question();
        $q->question = 'Is de Krtek een man of een vrouw?';
        $q->addAnswer(new Answer('Vrouw', true))
          ->addAnswer(new Answer('Man'));
        $q->ordering = 1;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Hoeveel broers heeft de Krtek?';
        $q->addAnswer(new Answer('Geen', true))
          ->addAnswer(new Answer('1'))
          ->addAnswer(new Answer('2'));
        $q->ordering = 2;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wat is de lievelingsfeestdag van de Krtek?';
        $q->addAnswer(new Answer('Geen'))
          ->addAnswer(new Answer('Diens eigen verjaardag'))
          ->addAnswer(new Answer('Koningsdag'))
          ->addAnswer(new Answer('Kerst', true))
          ->addAnswer(new Answer('Oud en Nieuw'));
        $q->ordering = 3;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Hoe kwam de Krtek naar Kersteren vandaag?';
        $q->addAnswer(new Answer('Met het OV', true))
          ->addAnswer(new Answer('Met de auto'));
        $q->ordering = 4;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Met wie keek de Krtek video bij binnenkomst?';
        $q->addAnswer(new Answer('Claudia'))
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
          ->addAnswer(new Answer('Tom', true));
        $q->ordering = 5;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Welk advies zou de Krtek zichzelf als kind geven?';
        $q->addAnswer(new Answer('Geef je vader een knuffel.'))
          ->addAnswer(new Answer('Trek je wat minder aan van anderen.'))
          ->addAnswer(new Answer('Luister meer naar je eigen gevoel in plaats van naar wat anderen vinden.'))
          ->addAnswer(new Answer('Stel niet alles tot het laatste moment uit.'))
          ->addAnswer(new Answer('Altijd doorgaan.'))
          ->addAnswer(new Answer('Probeer ook eens buiten de lijntjes te kleuren', true))
          ->addAnswer(new Answer('Ga als je groot bent op groepsreis! '))
          ->addAnswer(new Answer('Trek minder aan van de mening van anderen, het is oké om anders te zijn.'));
        $q->ordering = 6;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wat voor soort schoenen droeg de Krtek bij het diner?';
        $q->addAnswer(new Answer('Sneakers'))
          ->addAnswer(new Answer('Wandel-/bergschoenen', true))
          ->addAnswer(new Answer('Lederen schoenen'))
          ->addAnswer(new Answer('Pantoffels'))
          ->addAnswer(new Answer('Hakken'))
          ->addAnswer(new Answer('Geen schoenen, alleen sokken'));
        $q->ordering = 7;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Met welk vervoersmiddel reist de Krtek het liefste?';
        $q->addAnswer(new Answer('Fiets', true))
          ->addAnswer(new Answer('Auto'))
          ->addAnswer(new Answer('Trein'));
        $q->ordering = 8;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Heeft de Krtek een eigen auto?';
        $q->addAnswer(new Answer('Ja'))
          ->addAnswer(new Answer('Nee', true));
        $q->ordering = 9;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Van wie is de quote die de Krtek gepakt heeft';
        $q->addAnswer(new Answer('Karen'))
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
          ->addAnswer(new Answer('Sam, Davy', true));
        $q->ordering = 10;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Zou de Krtek molboekjes, jokers, vrijstellingen of topito’s uit iemands rugzak stelen om te kunnen winnen?';
        $q->addAnswer(new Answer('Ja'))
          ->addAnswer(new Answer('Nee', true));
        $q->ordering = 11;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'In wat voor bed slaapt de Krtek dit weekend?';
        $q->addAnswer(new Answer('Éénpersoons, losstaand bed'))
          ->addAnswer(new Answer('Éénpersoonsbed, tegen een ander bed aan', true))
          ->addAnswer(new Answer('Tweepersoons bed'));
        $q->ordering = 12;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Hoeveel jaar heeft de Krtek gedaan over de middelbare school?';
        $q->addAnswer(new Answer('5'))
          ->addAnswer(new Answer('6', true))
          ->addAnswer(new Answer('7'))
          ->addAnswer(new Answer('8'));
        $q->ordering = 13;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Waar zat de Krtek aan tafel bij het diner?';
        $q->addAnswer(new Answer('Met de rug naar de accommodatie'))
          ->addAnswer(new Answer('Met de rug naar de buitenmuur', true));
        $q->ordering = 14;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wie is de Krtek?';
        $q->addAnswer(new Answer('Claudia', true))
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
          ->addAnswer(new Answer('Tom'));
        $q->ordering = 15;
        $quiz->addQuestion($q);

        return $quiz;
    }

    private function createQuiz2(Season $season): Quiz
    {
        $quiz = new Quiz();
        $quiz->name = 'Quiz 2';
        $quiz->season = $season;

        $q = new Question();
        $q->question = 'Is de Krtek een man of een vrouw?';
        $q->addAnswer(new Answer('Man'))
          ->addAnswer(new Answer('Vrouw', true));
        $q->ordering = 1;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Heeft de Krtek dieetwensen of allergieën?';
        $q->addAnswer(new Answer('nee'))
          ->addAnswer(new Answer('De Krtek is vegetariër', true))
          ->addAnswer(new Answer('De Krtek is flexitariër'))
          ->addAnswer(new Answer('De Krtek heeft een allergie'))
          ->addAnswer(new Answer('De Krtek heeft een intolerantie'))
          ->addAnswer(new Answer('De Krtek eet geen rundvlees'))
          ->addAnswer(new Answer('De Krtek eet geen waterdieren'));
        $q->ordering = 2;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Hoe heet het huisdier/de huisdieren van de Krtek?';
        $q->addAnswer(new Answer('Amy, Karel en Floyd'))
          ->addAnswer(new Answer('Flip en Majoor'))
          ->addAnswer(new Answer('Benji'))
          ->addAnswer(new Answer('Sini'))
          ->addAnswer(new Answer('Tom'))
          ->addAnswer(new Answer('De huisdieren van de Krtek hebben geen naam'))
          ->addAnswer(new Answer('De Krtek heeft geen huisdieren', true));
        $q->ordering = 3;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wat dronk de Krtek deze ochtend bij het ontbijt?';
        $q->addAnswer(new Answer('Koffie'))
          ->addAnswer(new Answer('Thee'))
          ->addAnswer(new Answer('Water', true))
          ->addAnswer(new Answer('Melk'))
          ->addAnswer(new Answer('Sap'))
          ->addAnswer(new Answer('Niks'));
        $q->ordering = 4;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Waar ging de eerste vakantie die de Krtek zich nog herinnert heen?';
        $q->addAnswer(new Answer('Denemarken'))
          ->addAnswer(new Answer('Drenthe'))
          ->addAnswer(new Answer('Mallorca'))
          ->addAnswer(new Answer('Marokko'))
          ->addAnswer(new Answer('Oostenrijk'))
          ->addAnswer(new Answer('Turkije'))
          ->addAnswer(new Answer('Zweden', true));
        $q->ordering = 5;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Met welk groepje ging de Krtek als eerste het Douanespel in?';
        $q->addAnswer(new Answer('Het eerste groepje', true))
          ->addAnswer(new Answer('Het tweede groepje'))
          ->addAnswer(new Answer('Het derde groepje'))
          ->addAnswer(new Answer('Het vierde groepje'))
          ->addAnswer(new Answer('Het vijfde groepje'));
        $q->ordering = 6;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Gelooft de Krtek ergens in?';
        $q->addAnswer(new Answer('Nee'))
          ->addAnswer(new Answer('Het universum', true))
          ->addAnswer(new Answer('Toeval'))
          ->addAnswer(new Answer('De Krtek is hindoeïstisch'));
        $q->ordering = 7;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'At de Krtek op vrijdagavond heksenkaas tijdens het diner?';
        $q->addAnswer(new Answer('Ja', true))
          ->addAnswer(new Answer('Nee'));
        $q->ordering = 8;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Hoe laat ging de Krtek gisteravond naar bed?';
        $q->addAnswer(new Answer('Tussen 0:00 en 0:59 uur'))
          ->addAnswer(new Answer('Tussen 1:00 en 1:59 uur', true))
          ->addAnswer(new Answer('Tussen 2:00 en 2:59 uur'))
          ->addAnswer(new Answer('Na 3:00'));
        $q->ordering = 9;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Hoeveel batterijen heeft de Krtek naar het bord gebracht bij het douanespel?';
        $q->addAnswer(new Answer('1'))
          ->addAnswer(new Answer('2'))
          ->addAnswer(new Answer('3'))
          ->addAnswer(new Answer('geen', true));
        $q->ordering = 10;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wat keek de Krtek als kind graag op TV?';
        $q->addAnswer(new Answer('Digimon', true))
          ->addAnswer(new Answer('Floris'))
          ->addAnswer(new Answer('Het huis Anubis'))
          ->addAnswer(new Answer('Sesamstraat'))
          ->addAnswer(new Answer('Spongebob Squarepants'))
          ->addAnswer(new Answer('Teletubbies'));
        $q->ordering = 11;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Waarin zat op de heenreis de bagage van de Krtek (voornamelijk)?';
        $q->addAnswer(new Answer('In koffer(s)', true))
          ->addAnswer(new Answer('In losse tas(sen)'))
          ->addAnswer(new Answer('In een rugzak'));
        $q->ordering = 12;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Van welk geluid gaan de haren van de Krtek overeind staan?';
        $q->addAnswer(new Answer('Een vork die door een metalen pan krast '))
          ->addAnswer(new Answer('Smakkende mensen'))
          ->addAnswer(new Answer('Een vork die over een bord schraapt'))
          ->addAnswer(new Answer('Schuren met schuurpapier'))
          ->addAnswer(new Answer('Nagels op een krijtbord'))
          ->addAnswer(new Answer('Servies dat tegen elkaar klettert'))
          ->addAnswer(new Answer('Het geroekoe van een duif', true))
          ->addAnswer(new Answer('Piepschuim'));
        $q->ordering = 13;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wilde de Krtek penningmeester worden?';
        $q->addAnswer(new Answer('Ja'))
          ->addAnswer(new Answer('Nee', true));
        $q->ordering = 14;
        $quiz->addQuestion($q);

        $q = new Question();
        $q->question = 'Wie is de Krtek?';
        $q->addAnswer(new Answer('Claudia', true))
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
          ->addAnswer(new Answer('Tom'));
        $q->ordering = 15;
        $quiz->addQuestion($q);

        return $quiz;
    }
}
