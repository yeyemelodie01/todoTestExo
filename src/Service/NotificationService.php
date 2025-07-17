<?php

namespace App\Service;

use App\Entity\Task;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {}

    public function sendTaskReminder(Task $task): void
    {
        try {
            $email = (new Email())
                ->from('noreply@todotestingapp.com')
                ->to('user@example.com')
                ->subject('Rappel de tâche: ' . $task->getTitle())
                ->html($this->buildReminderEmailContent($task));

            $this->mailer->send($email);
            
            $this->logger->info('Task reminder sent', [
                'task_id' => $task->getId(),
                'task_title' => $task->getTitle()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to send task reminder', [
                'task_id' => $task->getId(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function sendTaskCompletionNotification(Task $task): void
    {
        try {
            $email = (new Email())
                ->from('noreply@todotestingapp.com')
                ->to('user@example.com')
                ->subject('Tâche terminée: ' . $task->getTitle())
                ->html($this->buildCompletionEmailContent($task));

            $this->mailer->send($email);
            
            $this->logger->info('Task completion notification sent', [
                'task_id' => $task->getId(),
                'task_title' => $task->getTitle()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to send completion notification', [
                'task_id' => $task->getId(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function sendReminderForOverdueTasks(): int
    {
        // Cette méthode sera appelée par le TaskService
        // pour envoyer des rappels pour les tâches en retard
        
        $this->logger->info('Starting overdue task reminder process');
        
        // Le service sera injecté avec la liste des tâches en retard
        // Cette méthode sera utilisée dans les tests d'intégration
        
        return 0; // Placeholder - sera implémenté dans les tests
    }

    private function buildReminderEmailContent(Task $task): string
    {
        $dueDate = $task->getDueDate() ? $task->getDueDate()->format('d/m/Y') : 'Non définie';
        $priority = ucfirst($task->getPriority());
        
        return "
            <h2>Rappel de tâche</h2>
            <p><strong>Titre:</strong> {$task->getTitle()}</p>
            <p><strong>Description:</strong> {$task->getDescription()}</p>
            <p><strong>Priorité:</strong> {$priority}</p>
            <p><strong>Date limite:</strong> {$dueDate}</p>
            <p>N'oubliez pas de compléter cette tâche !</p>
        ";
    }

    private function buildCompletionEmailContent(Task $task): string
    {
        $completedAt = $task->getCompletedAt()->format('d/m/Y à H:i');
        
        return "
            <h2>Tâche terminée</h2>
            <p><strong>Titre:</strong> {$task->getTitle()}</p>
            <p><strong>Terminée le:</strong> {$completedAt}</p>
            <p>Félicitations pour avoir terminé cette tâche !</p>
        ";
    }
}