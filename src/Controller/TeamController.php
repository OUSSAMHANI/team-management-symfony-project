<?php
namespace App\Controller;

use App\Entity\Team;
use App\Entity\Task;
use App\Form\TeamType;
use App\Repository\TaskRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/team')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'team_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/new', name: 'team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set team members (ensure cascade persist is used in the entity)
            foreach ($team->getMembers() as $member) {
                $member->setTeam($team);
            }

            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_list');
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($team->getMembers() as $member) {
                $member->setTeam($team);
            }

            $entityManager->flush();

            return $this->redirectToRoute('team_list');
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'team_delete', methods: ['POST'])]
    public function delete(Team $team, EntityManagerInterface $entityManager): Response
    {
        // Cascade delete members if needed (configured in the entity)
        $entityManager->remove($team);
        $entityManager->flush();

        return $this->redirectToRoute('team_list');
    }

    // Show Team Details
    #[Route('/{id}/show', name: 'team_show', methods: ['GET'])]
    public function show(Team $team, TaskRepository $taskRepository, ProjectRepository $projectRepository): Response
    {
        // Get all tasks for each team member
        $teamMembers = $team->getMembers();
        $teamProjects = $team->getProjects();

        $memberTasks = [];
        foreach ($teamMembers as $member) {
            $tasks = $taskRepository->findBy(['assignedTo' => $member]);
            $memberTasks[$member->getId()] = $tasks;  // Map tasks by member ID
        }

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'members' => $teamMembers,
            'projects' => $teamProjects,
            'memberTasks' => $memberTasks,
        ]);
    }
}
