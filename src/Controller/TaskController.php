<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Service\TaskService;
use App\Service\CategoryService;
use App\Service\TaskStatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskService $taskService,
        private CategoryService $categoryService,
        private TaskStatisticsService $statisticsService
    ) {}

    #[Route('/', name: 'task_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $filter = $request->query->get('filter', 'all');
        
        $tasks = match($filter) {
            'completed' => $this->taskService->getCompletedTasks(),
            'pending' => $this->taskService->getPendingTasks(),
            'overdue' => $this->taskService->getOverdueTasks(),
            default => $this->taskService->getAllTasks()
        };

        $statistics = $this->statisticsService->getCompleteStatistics();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'stats' => $statistics->toArray(),
            'filter' => $filter,
        ]);
    }

    #[Route('/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->taskService->createTask(
                    $task->getTitle(),
                    $task->getDescription(),
                    $task->getCategory()
                );

                $this->addFlash('success', 'Tâche créée avec succès !');
                return $this->redirectToRoute('task_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->taskService->updateTask(
                    $task,
                    $task->getTitle(),
                    $task->getDescription()
                );

                $this->addFlash('success', 'Tâche modifiée avec succès !');
                return $this->redirectToRoute('task_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/complete', name: 'task_complete', methods: ['POST'])]
    public function complete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('complete'.$task->getId(), $request->request->get('_token'))) {
            try {
                $this->taskService->completeTask($task);
                $this->addFlash('success', 'Tâche marquée comme terminée !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/{id}/reopen', name: 'task_reopen', methods: ['POST'])]
    public function reopen(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('reopen'.$task->getId(), $request->request->get('_token'))) {
            try {
                $this->taskService->reopenTask($task);
                $this->addFlash('success', 'Tâche rouverte !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            try {
                $this->taskService->deleteTask($task);
                $this->addFlash('success', 'Tâche supprimée !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/statistics', name: 'task_statistics', methods: ['GET'])]
    public function statistics(): Response
    {
        $statistics = $this->statisticsService->getCompleteStatistics();

        return $this->render('task/statistics.html.twig', [
            'statistics' => $statistics,
        ]);
    }
}