<?php

namespace App\Entity\PlayOff;

use App\Entity\Division\Division;
use App\Entity\Division\ScoreTableRow;
use App\Entity\Game\Game;
use App\Entity\PlayOff\Exception\MustBeEvenCountPlayersException;

class PlayOffFactory
{
    public static function createFromDivision(Division ...$divisions): PlayOffStep
    {
        $tableRows = [];
        foreach ($divisions as $division) {
            $table = $division->toScoreTable();
            $tableRows = [...$tableRows, ...$table->winners()];
        }
        if (0 !== count($tableRows) % 2) {
            throw new MustBeEvenCountPlayersException();
        }

        usort($tableRows, fn(ScoreTableRow $a, ScoreTableRow $b) => $a->points() > $b->points() ? -1 : 1);

        $teams = array_values(array_map(fn(ScoreTableRow $row) => $row->team(), $tableRows));

        $count = count($teams);

        $games = [];
        for ($i = 0; $i < $count / 2; $i++) {
            $games[] = new Game($teams[$i], $teams[$count - $i - 1]);
        }

        return new PlayOffStep($games);
    }
}