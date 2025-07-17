<?php

namespace App\EventListener;

use App\Event\TaskCompletedEvent;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TaskCompletedListener
{
    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function onTaskCompleted(TaskCompletedEvent $event): void
    {
        $task = $event->getTask();
        
        $this->logger->info('Task completed', [
            'task_id' => $task->getId(),
            'task_title' => $task->getTitle(),
            'completed_at' => $event->getCompletedAt()->format('Y-m-d H:i:s')
        ]);

        // Envoyer une notification
        try {
            $this->notificationService->sendTaskCompletionNotification($task);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send task completion notification', [
                'task_id' => $task->getId(),
                'error' => $e->getMessage()
            ]);
        }

        // Autres actions possibles:
        // - Incrémenter des statistiques
        // - Mettre à jour des caches
        // - Déclencher d'autres workflows
    }
}