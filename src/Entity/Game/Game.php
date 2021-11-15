<?php declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Game\Exception\GameNotCompletedException;
use App\Entity\Game\Exception\TeamNotPlayInGameException;
use App\Entity\Team\Team;
use App\Repository\DivisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="gamesPartOne")
     */
    private $team_one;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class,inversedBy="gamesPartTwo")
     */
    private $team_two;

    /**
     * @ORM\Column(type="integer")
     */
    private $team_one_goal;

    /**
     * @ORM\Column(type="integer")
     */
    private $team_two_goal;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamOne(): ?Team
    {
        return $this->team_one;
    }

    public function setTeamOne(?Team $team_one): self
    {
        $this->team_one = $team_one;

        return $this;
    }

    public function getTeamTwo(): ?Team
    {
        return $this->team_two;
    }

    public function setTeamTwo(?Team $team_two): self
    {
        $this->team_two = $team_two;

        return $this;
    }

    public function getTeamOneGoal(): ?int
    {
        return $this->team_one_goal;
    }

    public function setTeamOneGoal(int $team_one_goal): self
    {
        $this->team_one_goal = $team_one_goal;

        return $this;
    }

    public function getTeamTwoGoal(): ?int
    {
        return $this->team_two_goal;
    }

    public function setTeamTwoGoal(int $team_two_goal): self
    {
        $this->team_two_goal = $team_two_goal;

        return $this;
    }

    public function __construct(Team $teamOne, Team $teamTwo)
    {
        $this->team_one = $teamOne;
        $this->team_two = $teamTwo;
        $this->result = GameResult::createScheduled();
    }

    public function complete(GameResult $result): void
    {
        $this->setTeamOneGoal($result->goalForTeamOne());
        $this->setTeamTwoGoal($result->goalForTeamTwo());
    }

    public function hasTeam(Team $team): bool
    {
        return $team->isEqual($this->team_one) || $team->isEqual($this->team_two);
    }


    public function matchScores(Team $team): string
    {
        $result = GameResult::createForGame($this);
        if ($team->isEqual($this->team_one)) {
            return sprintf('%d : %d', $result->goalForTeamOne(), $result->goalForTeamTwo());
        }

        if ($team->isEqual($this->team_two)) {
            return sprintf('%d : %d', $result->goalForTeamTwo(), $result->goalForTeamOne());
        }

        throw new TeamNotPlayInGameException();
    }

    public function teamPoints(Team $team): int
    {
        $result = GameResult::createForGame($this);
        if ($team->isEqual($this->team_one)) {
            return $result->scoresTeamOne();
        }

        if ($team->isEqual($this->team_two)) {
            return $result->scoresTeamTwo();
        }

        throw new TeamNotPlayInGameException();
    }

    public function winner(GameResult $result): ?Team
    {

        if (GameResult::SCORE_WIN === $result->scoresTeamOne()) {
            return $this->team_one;
        }

        if (GameResult::SCORE_WIN === $result->scoresTeamTwo()) {
            return $this->team_two;
        }

        return null;
    }

}