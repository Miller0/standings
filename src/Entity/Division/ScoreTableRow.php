<?php declare(strict_types=1);

namespace App\Entity\Division;


use App\Entity\Game\Game;
use App\Entity\Team\Team;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\Collection;

class ScoreTableRow
{
    public Team $team;
    public ArrayCollection $games;
    public int $scores = 0;

    /**
     * @param Game[] $games
     */
    public function __construct(Team $team, ArrayCollection $games)
    {
        $this->team = $team;
        $this->games = $games;

        foreach ($games as $game) {
            $this->scores += $game->teamPoints($team);
        }
    }

    public function team(): Team
    {
        return $this->team;
    }

    public function findGameForTeam(Team $teamTwo): ?Game
    {
        if ($this->team->isEqual($teamTwo)) {
            return null;
        }

        foreach ($this->games as $game) {
            if ($game->hasTeam($teamTwo)) {
                return $game;
            }
        }

        return null;
    }

    public function points(): int
    {
        return $this->scores;
    }
}