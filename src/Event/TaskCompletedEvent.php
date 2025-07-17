<?php

namespace App\Event;

use App\Entity\Task;
use Symfony\Contracts\EventDispatcher\Event;

class TaskCompletedEvent extends Event
{
    public const NAME = 'task.completed';

    public function __construct(
        private Task $task,
        private \DateTimeInterface $completedAt
    ) {}

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getCompletedAt(): \DateTimeInterface
    {
        return $this->completedAt;
    }
}