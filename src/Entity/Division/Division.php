<?php

namespace App\Entity\Division;

use App\Entity\Game\Game;
use App\Entity\Team\Team;
use App\Repository\DivisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DivisionRepository::class)
 */
class Division
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="division", cascade={"persist", "remove"})
     */
    private $teams;

    private Collection $games;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->teams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setGame(Game $game): self
    {
        if (empty($this->games)) {
            $this->games = new ArrayCollection();
        }
        $this->games->add($game);
        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setDivision($this);
        }

        return $this;
    }

    public function addTeams(Team  ...$teams): void
    {
        foreach ($teams as $team) {
            $this->addTeam($team);
        }
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getDivision() === $this) {
                $team->setDivision(null);
            }
        }

        return $this;
    }

    public function findGameForTeams(Team $teamOne, Team $teamTwo): ?Game
    {
        if (empty($this->games)) {
            return null;
        }

        if ($teamOne->isEqual($teamTwo)) {
            return null;
        }

        foreach ($this->games as $game) {
            if ($game->hasTeam($teamOne) && $game->hasTeam($teamTwo)) {
                return $game;
            }
        }
        return null;
    }

    /**
     * @return ScoreTable
     */
    public function toScoreTable(): ScoreTable
    {
        return new ScoreTable($this);
    }
}
