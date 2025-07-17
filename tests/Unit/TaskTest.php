<?php

use App\Entity\Task;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    // Création d'une tâche valide
    public function testAddTask()
    {
        $task = new Task();
        $task->setTitle('Test Task')
             ->setDescription('This is a test task')
             ->setDueDate(new \DateTimeImmutable('2023-12-31'))
             ->setPriority('medium')
             ->setCreatedAt(new \DateTimeImmutable());

        $this->assertInstanceOf(Task::class, $task);
    }

    // Setter / Getter du titre
    public function testSetGetTitle()
    {
        $task = new Task();
        $task->setTitle('Ma Tâche');

        $this->assertEquals('Ma Tâche', $task->getTitle());
    }

    // Rejet d'un titre vide
    public function testSetEmptyTitleThrowsException(): void
    {
        $task = new Task();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre ne peut pas être vide.');

        $task->setTitle('');
    }

    // Setter / Getter de la description
    public function testSetDescription()
    {
        $task = new Task();
        $task->setDescription('Ceci est une description test');
    }

}