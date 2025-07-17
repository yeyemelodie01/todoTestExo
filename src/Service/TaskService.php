<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\Category;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\TaskCompletedEvent;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function createTask(string $title, ?string $description = null, ?Category $category = null): Task
    {
        $task = new Task();
        $task->setTitle($title);
        
        if ($description) {
            $task->setDescription($description);
        }
        
        if ($category) {
            $task->setCategory($category);
        }

        $this->validateTask($task);
        $this->taskRepository->save($task, true);

        return $task;
    }

    public function createTaskWithValidation(string $title, ?string $description = null): Task
    {
        // Validation personnalisée
        if (strlen($title) < 3) {
            throw new \InvalidArgumentException('Le titre doit contenir au moins 3 caractères');
        }
        
        if (strlen($title) > 100) {
            throw new \InvalidArgumentException('Le titre ne peut pas dépasser 100 caractères');
        }
        
        // Vérifier les doublons
        $existingTask = $this->taskRepository->findTaskByTitle($title);
        if ($existingTask) {
            throw new \InvalidArgumentException('Une tâche avec ce titre existe déjà');
        }
        
        return $this->createTask($title, $description);
    }

    public function updateTask(Task $task, string $title, ?string $description = null): Task
    {
        $task->setTitle($title);
        $task->setDescription($description);

        $this->validateTask($task);
        $this->taskRepository->save($task, true);

        return $task;
    }

    public function completeTask(Task $task): Task
    {
        $task->markAsCompleted();
        $this->taskRepository->save($task, true);

        // Dispatch event
        $event = new TaskCompletedEvent($task, $task->getCompletedAt());
        $this->eventDispatcher->dispatch($event, TaskCompletedEvent::NAME);

        return $task;
    }

    public function completeTaskWithEvent(Task $task): Task
    {
        return $this->completeTask($task);
    }

    public function reopenTask(Task $task): Task
    {
        $task->markAsIncomplete();
        $this->taskRepository->save($task, true);

        return $task;
    }

    public function deleteTask(Task $task): void
    {
        $this->taskRepository->remove($task, true);
    }

    public function assignTaskToCategory(Task $task, Category $category): void
    {
        $task->setCategory($category);
        $this->taskRepository->save($task, true);
    }

    public function setPriority(Task $task, string $priority): Task
    {
        $task->setPriority($priority);
        $this->taskRepository->save($task, true);

        return $task;
    }

    public function setDueDate(Task $task, ?\DateTimeImmutable $dueDate): Task
    {
        $task->setDueDate($dueDate);
        $this->taskRepository->save($task, true);

        return $task;
    }

    // Finders
    public function getAllTasks(): array
    {
        return $this->taskRepository->findAllOrderedByCreatedAt();
    }

    public function getTasksByStatus(bool $completed): array
    {
        return $this->taskRepository->findByStatus($completed);
    }

    public function getCompletedTasks(): array
    {
        return $this->taskRepository->findCompletedTasks();
    }

    public function getPendingTasks(): array
    {
        return $this->taskRepository->findPendingTasks();
    }

    public function getOverdueTasks(): array
    {
        return $this->taskRepository->findOverdueTasks();
    }

    public function getTasksByCategory(Category $category): array
    {
        return $this->taskRepository->findByCategory($category);
    }

    public function getTasksByPriority(string $priority): array
    {
        return $this->taskRepository->findByPriority($priority);
    }

    public function getTasksDueSoon(int $days = 7): array
    {
        return $this->taskRepository->findTasksDueSoon($days);
    }

    public function getTasksWithCategories(): array
    {
        return $this->taskRepository->findTasksWithCategories();
    }

    // Statistics
    public function getTaskStatistics(): array
    {
        return [
            'total' => $this->taskRepository->count([]),
            'completed' => $this->taskRepository->countCompletedTasks(),
            'pending' => $this->taskRepository->countPendingTasks(),
            'overdue' => $this->taskRepository->countOverdueTasks(),
        ];
    }

    public function getTaskStatisticsOptimized(): array
    {
        return $this->taskRepository->getStatisticsOptimized();
    }

    public function findTaskById(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }

    private function validateTask(Task $task): void
    {
        $violations = $this->validator->validate($task);
        
        if (count($violations) > 0) {
            throw new \InvalidArgumentException(
                'Validation failed: ' . (string) $violations
            );
        }
    }
}