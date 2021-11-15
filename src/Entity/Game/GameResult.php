<?php declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Team\Team;
use JetBrains\PhpStorm\Pure;

class GameResult
{

    public const SCORE_WIN = 2;
    public const SCORE_DRAW = 1;
    public const SCORE_FAIL = 0;

    private int $goalsTeamOne;
    private int $goalsTeamTwo;

    public function __construct(int $goalsTeamOne, int $goalsTeamTwo)
    {
        $this->goalsTeamOne = $goalsTeamOne;
        $this->goalsTeamTwo = $goalsTeamTwo;
    }

    #[Pure] public static function createScheduled(): self
    {
        return new self(0, 0);
    }

    #[Pure] public static function createCompleted(int $goalsTeamOne, int $goalsTeamTwo): self
    {
        return new self($goalsTeamOne, $goalsTeamTwo);
    }

    #[Pure] public static function createForGame(Game $game): self
    {
        return new self($game->getTeamOneGoal(), $game->getTeamTwoGoal());
    }

    public function scoresTeamOne(): int
    {
        if ($this->goalsTeamOne === $this->goalsTeamTwo) {
            return self::SCORE_DRAW;
        }

        if ($this->goalsTeamOne > $this->goalsTeamTwo) {
            return self::SCORE_WIN;
        }

        return self::SCORE_FAIL;
    }

    public function scoresTeamTwo(): int
    {
        if ($this->goalsTeamOne === $this->goalsTeamTwo) {
            return self::SCORE_DRAW;
        }
        if ($this->goalsTeamOne < $this->goalsTeamTwo) {
            return self::SCORE_WIN;
        }

        return self::SCORE_FAIL;
    }

    public function goalForTeamOne(): int
    {
        return $this->goalsTeamOne;
    }

    public function goalForTeamTwo(): int
    {
        return $this->goalsTeamTwo;
    }



}