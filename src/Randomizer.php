<?php declare(strict_types=1);

namespace App;


use App\Entity\Division\Division;
use App\Entity\Game\GameResult;
use App\Entity\PlayOff\PlayOffStep;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class Randomizer
{
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $divisions
     * @throws \Doctrine\DBAL\Exception
     */
    public function randomizeDivisionsGamesResults(Division ... $divisions)
    {
        $this->entityManager->getConnection()->executeQuery('TRUNCATE TABLE game');
        foreach ($divisions as $division) {
            $this->randomizeDivisionGamesResults($division);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function randomizeDivisionGamesResults(Division $division): void
    {
        foreach ($division->getTeams() as $team) {
            $team->divisionMakeAllGames();
            foreach ($team->allGames() as $game) {
                $game->complete(new GameResult(rand(0, 5), rand(0, 5)));
                $this->entityManager->persist($game);
                $this->entityManager->flush();
            }
        }
    }


    public function randomizePlayOffStep(PlayOffStep $playOffStep): void
    {
        foreach ($playOffStep->games() as $game) {
            $score = $this->generateNotEqualScore();
            $game->complete(GameResult::createCompleted($score[0], $score[1]));
        }
    }

    private function generateNotEqualScore(): array
    {
        $score = [random_int(0, 5), random_int(0, 5)];
        if ($score[0] === $score[1]) {
            $score[0] = ++$score[0];
        }
        return $score;
    }

}