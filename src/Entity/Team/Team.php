<?php

namespace App\Entity\Team;

use App\Entity\Division\Division;
use App\Entity\Game\Game;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Division::class, inversedBy="teams")
     */
    private $division;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;


    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="team_one",orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $gamesPartOne;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="team_two",orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $gamesPartTwo;

    private Collection $games;


    public function __construct(string $name, int $division_scores = 0)
    {
        $this->name = $name;
        $this->division_scores = $division_scores;
        $this->playoffs = new ArrayCollection();
        $this->games = new ArrayCollection();
    }


    public function getGames(): ArrayCollection
    {
        if (!empty($this->gamesPartOne) || $this->gamesPartTwo) {
            return new ArrayCollection(
                array_merge($this->gamesPartOne->toArray(), $this->gamesPartTwo->toArray())
            );
        }
        return new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDivision(): ?Division
    {
        return $this->division;
    }

    public function setDivision(?Division $division): self
    {
        $this->division = $division;

        return $this;
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

    public function isEqual(Team $team): bool
    {
        return $this->id === $team->id;
    }


    public function allGames(): ArrayCollection|Collection
    {
        return $this->games;
    }

    public function divisionMakeAllGames(): void
    {
        foreach ($this->division->getTeams() as $divisionTeam) {

            if (empty($this->games)) {
                $this->games = new ArrayCollection();
            }

            if ($this->isEqual($divisionTeam)) {
                continue;
            }

            if ($this->division->findGameForTeams($this, $divisionTeam)) {
                continue;
            }

            $game = new Game($this, $divisionTeam);

            $this->games->add($game);
            $this->division->setGame($game);
        }
    }

}
