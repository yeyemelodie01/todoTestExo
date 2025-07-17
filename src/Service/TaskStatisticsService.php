<?php

namespace App\Service;

use App\Entity\TaskStatistics;
use App\Repository\TaskRepository;

class TaskStatisticsService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    public function getCompleteStatistics(): TaskStatistics
    {
        $basicStats = $this->taskRepository->getStatisticsOptimized();
        $byPriority = $this->taskRepository->getTaskCountByPriority();
        $byCategory = $this->taskRepository->getTaskCountByCategory();

        return new TaskStatistics(
            total: $basicStats['total'],
            completed: $basicStats['completed'],
            pending: $basicStats['pending'],
            overdue: $basicStats['overdue'],
            byPriority: $byPriority,
            byCategory: $byCategory
        );
    }

    public function getBasicStatistics(): array
    {
        return $this->taskRepository->getStatisticsOptimized();
    }

    public function getPriorityStatistics(): array
    {
        return $this->taskRepository->getTaskCountByPriority();
    }

    public function getCategoryStatistics(): array
    {
        return $this->taskRepository->getTaskCountByCategory();
    }
}