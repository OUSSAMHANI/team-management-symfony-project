<?php
// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use App\Entity\Project;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // List all tasks with the option to filter by project and status
    #[Route('/', name: 'task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, ProjectRepository $projectRepository, Request $request): Response
    {
        $projects = $projectRepository->findAll();
        
        // Get filter values from request
        $selectedProjectId = $request->query->get('project', null);
        $selectedStatus = $request->query->get('status', null);

        // Build query based on filters
        $criteria = [];
        
        if ($selectedProjectId) {
            $criteria['project'] = $selectedProjectId;
        }
        
        if ($selectedStatus) {
            $criteria['status'] = $selectedStatus;
        }

        $tasks = $taskRepository->findBy($criteria);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'projects' => $projects,
            'selectedProject' => $selectedProjectId,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    // Create a new task
    #[Route('/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'Task created successfully!');
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    // Show the details of a single task
    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    // Edit an existing task
    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Task updated successfully!');
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    // Delete a task
    #[Route('/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'Task deleted successfully!');
        }

        return $this->redirectToRoute('task_index');
    }

    // Link a task to a project (if this is part of the logic)
    #[Route('/{id}/assign-project', name: 'task_assign_project', methods: ['GET', 'POST'])]
    public function assignProject(Request $request, Task $task, ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();
        $form = $this->createFormBuilder($task)
            ->add('project', null, ['choices' => $projects])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Project assigned to task successfully!');
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/assign_project.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }
}
