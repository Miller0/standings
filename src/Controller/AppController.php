<?php declare(strict_types=1);

namespace App\Controller;


use App\Entity\Division\Division;
use App\Entity\Game\Game;
use App\Entity\Game\GameResult;
use App\Entity\PlayOff\PlayOffFactory;
use App\Entity\Team\Team;
use App\Randomizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AppController extends AbstractController
{
    private Randomizer $randomizer;

    public function __construct(Randomizer $randomizer)
    {
        $this->randomizer = $randomizer;
    }

    #[Route(path: '/', name: 'main', methods: ['GET'])]
    public function table(Request $request): Response
    {

        $divisions = $this->getDoctrine()
            ->getRepository(Division::class)
            ->findAll();

        if (empty($divisions) || $request->query->get('generate')) {
            $this->generate();
            return $this->redirect('/');
        }

        $tableScores = array_map(fn(Division $division) => $division->toScoreTable(), $divisions);

        $playOff = PlayOffFactory::createFromDivision(...$divisions);
        $this->randomizer->randomizePlayOffStep($playOff);
        $playOffSteps = [$playOff];

        while (!$playOff->isFinal()) {
            $playOff = $playOff->nextStep();
            $this->randomizer->randomizePlayOffStep($playOff);
            $playOffSteps[] = $playOff;
        }

        return $this->render('main.html.twig', [
            'tableScores' => $tableScores,
            'playOffSteps' => $playOffSteps
        ]);
    }


    private function generate()
    {
        $divisions = [
            $this->generateDivision('Division A', 'ES', 'EE', 'BY', 'UA'),
            $this->generateDivision('Division B', 'RU', 'LT', 'DE', 'IT'),
        ];

        $this->randomizer->randomizeDivisionsGamesResults(...$divisions);
    }

    private function generateDivision(string $title, string ...$teams): Division
    {
        $em = $this->getDoctrine()->getManager();

        $division = $this->getDoctrine()
            ->getRepository(Division::class)
            ->findOneBy(['name' => $title]);

        if (empty($division)) {
            $division = new Division($title);
            $division->addTeams(...array_map(fn(string $teamName) => new Team($teamName), $teams));
            $em->persist($division);
            $em->flush();
        }

        return $division;
    }


}