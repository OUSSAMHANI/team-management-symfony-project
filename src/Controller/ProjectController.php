<?php

// src/Controller/ProjectController.php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\Task;
use App\Form\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'project_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // No date-related checks here anymore
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project, EntityManagerInterface $entityManager): Response
    {
        // Fetching all tasks related to the project (no date handling)
        $tasks = $entityManager->getRepository(Task::class)->findBy(['project' => $project]);
    
        // Calculate progress based on task completion (without date-related checks)
        $completedTasks = array_filter($tasks, function($task) {
            return $task->getStatus() === 'Done'; // 'Done' status for completed tasks
        });
    
        $totalTasks = count($tasks);
        $progress = $totalTasks > 0 ? (count($completedTasks) / $totalTasks * 100) : 0;
    
        // Set progress bar color based on progress percentage
        if ($progress < 40) {
            $progressBarColor = 'bg-danger'; // Red color for low progress
        } elseif ($progress < 70) {
            $progressBarColor = 'bg-warning'; // Yellow color for moderate progress
        } else {
            $progressBarColor = 'bg-success'; // Green color for high progress
        }
    
        return $this->render('project/show.html.twig', [
            'project' => $project,
            'tasks' => $tasks, // Pass tasks to the template
            'progress' => $progress, // Pass progress to the template
            'progressBarColor' => $progressBarColor, // Pass progress bar color
        ]);
    }
    
    #[Route('/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // No date-related checks here anymore
            $entityManager->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'project_delete', methods: ['POST'])]
    public function delete(Project $project, EntityManagerInterface $entityManager): Response
    {
        // Cascade delete tasks and users if needed (configured in the entity itself)
        $entityManager->remove($project);
        $entityManager->flush();

        return $this->redirectToRoute('project_index');
    }

    #[Route('/{id}/assign-user', name: 'project_assign_user', methods: ['POST'])]
    public function assignUser(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        // Check for user existence
        $userId = $request->request->get('user');
        $user = $entityManager->getRepository(User::class)->find($userId);

        if ($user) {
            // Check if the user is already assigned to the project
            if (!$project->getUsers()->contains($user)) {
                $project->addUser($user);
                $entityManager->flush();
                $this->addFlash('success', 'User successfully assigned to the project.');
            } else {
                $this->addFlash('warning', 'User is already assigned to this project.');
            }
        } else {
            $this->addFlash('danger', 'User not found.');
        }

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }
}
