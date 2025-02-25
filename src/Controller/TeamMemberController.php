<?php
// src/Controller/TeamMemberController.php
namespace App\Controller;

use App\Entity\TeamMember;
use App\Entity\Task;
use App\Entity\Project;
use App\Form\TeamMemberType;
use App\Repository\TeamRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\TaskRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TeamMemberController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/team-member', name: 'team_member_list')]
    public function index(
        TeamMemberRepository $teamMemberRepository, 
        TeamRepository $teamRepository, 
        Request $request
    ): Response {
        // Fetch all teams for the dropdown
        $teams = $teamRepository->findAll();

        // Get the team filter from the request
        $selectedTeamName = $request->query->get('team', null);

        if ($selectedTeamName) {
            // Filter team members by the selected team using the repository
            $teamMembers = $teamMemberRepository->findByTeamName($selectedTeamName);
        } else {
            // Fetch all team members if no filter is applied
            $teamMembers = $teamMemberRepository->findAll();
        }

        return $this->render('team_member/index.html.twig', [
            'members' => $teamMembers,
            'teams' => $teams,
            'selectedTeam' => $selectedTeamName,
        ]);
    }

    #[Route('/team-member/new', name: 'team_member_new')]
    public function new(Request $request): Response
    {
        $teamMember = new TeamMember();
        $form = $this->createForm(TeamMemberType::class, $teamMember);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new team member to the database
            $this->entityManager->persist($teamMember);
            $this->entityManager->flush();

            // Success message
            $this->addFlash('success', 'Team member added successfully!');
            return $this->redirectToRoute('team_member_list');
        }

        return $this->render('team_member/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/team-member/{id}/edit', name: 'team_member_edit')]
    public function edit(Request $request, TeamMember $teamMember): Response
    {
        $form = $this->createForm(TeamMemberType::class, $teamMember);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the changes to the team member
            $this->entityManager->flush();

            // Success message
            $this->addFlash('success', 'Team member updated successfully!');
            return $this->redirectToRoute('team_member_list');
        }

        return $this->render('team_member/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/team-member/{id}/show', name: 'team_member_show')]
    public function show(TeamMember $teamMember, TaskRepository $taskRepository, ProjectRepository $projectRepository): Response
    {
        // Get assigned tasks and projects
        $assignedTasks = $taskRepository->findBy(['assignedTo' => $teamMember]);
        $assignedProjects = $projectRepository->findBy(['team' => $teamMember->getTeam()]);

        return $this->render('team_member/show.html.twig', [
            'teamMember' => $teamMember,
            'assignedTasks' => $assignedTasks,
            'assignedProjects' => $assignedProjects,
        ]);
    }

    #[Route('/team-member/{id}/delete', name: 'team_member_delete')]
    public function delete(TeamMember $teamMember): Response
    {
        $this->entityManager->remove($teamMember);
        $this->entityManager->flush();

        // Success message
        $this->addFlash('success', 'Team member deleted successfully!');
        return $this->redirectToRoute('team_member_list');
    }
}
