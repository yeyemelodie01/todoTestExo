<?php

namespace App\Entity;

/**
 * Value Object pour les statistiques des tâches
 */
class TaskStatistics
{
    public function __construct(
        private int $total,
        private int $completed,
        private int $pending,
        private int $overdue,
        private array $byPriority = [],
        private array $byCategory = []
    ) {}

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCompleted(): int
    {
        return $this->completed;
    }

    public function getPending(): int
    {
        return $this->pending;
    }

    public function getOverdue(): int
    {
        return $this->overdue;
    }

    public function getByPriority(): array
    {
        return $this->byPriority;
    }

    public function getByCategory(): array
    {
        return $this->byCategory;
    }

    public function getCompletionRate(): float
    {
        if ($this->total === 0) {
            return 0.0;
        }
        
        return ($this->completed / $this->total) * 100;
    }

    public function getOverdueRate(): float
    {
        if ($this->total === 0) {
            return 0.0;
        }
        
        return ($this->overdue / $this->total) * 100;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'completed' => $this->completed,
            'pending' => $this->pending,
            'overdue' => $this->overdue,
            'completion_rate' => $this->getCompletionRate(),
            'overdue_rate' => $this->getOverdueRate(),
            'by_priority' => $this->byPriority,
            'by_category' => $this->byCategory,
        ];
    }
}