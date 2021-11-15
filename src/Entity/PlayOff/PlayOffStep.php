<?php

namespace App\Entity\PlayOff;

use App\Entity\Game\Game;
use App\Entity\Game\GameResult;
use App\Entity\PlayOff\Exception\MustBeEvenCountPlayersException;
use App\Entity\PlayOff\Exception\PlayOffGameNotWinnerException;
use App\Entity\PlayOff\Exception\PlayOffNotCompletedException;
use App\Entity\Team\Team;

class PlayOffStep
{

    /**
     * @var Game[]
     */
    private array $games;

    /**
     * @param Game[] $games
     */
    public function __construct(array $games)
    {
        $this->games = $games;
        $this->title = sprintf('1/%d', count($this->games));
    }

    public function nextStep(): self
    {
        /** @var Team[] $winners */
        $winners = [];
        foreach ($this->games as $game) {
            $winner = $game->winner(GameResult::createForGame($game)) ?? throw new PlayOffGameNotWinnerException();
            $winners[] = $winner;
        }

        $countWinners = count($winners);

        if (0 !== $countWinners % 2) {
            throw new MustBeEvenCountPlayersException();
        }

        $games = [];
        for ($i = 0; $i < $countWinners / 2; $i++) {
            $games[] = new Game($winners[$i * 2], $winners[$i * 2 + 1]);
        }

        return new self($games);
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return Game[]
     */
    public function games(): array
    {
        return $this->games;
    }

    public function isFinal(): bool
    {
        return 1 === count($this->games);
    }
}