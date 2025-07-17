<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $titles = [
            'Buy groceries',
            'Walk the dog',
            'Finish homework',
            'Clean the house',
            'Prepare dinner',
            'Read a book',
            'Exercise for 30 minutes',
            'Call mom',
            'Organize the garage',
            'Plan the weekend trip'
        ];

        $priorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($titles as $i => $title) {
            $priority = $priorities[array_rand($priorities)];

            $task = new Task();
            $task->setTitle($title);
            $task->setDescription('Description for ' . $title);
            $task->setPriority($priority);
            $task->setDueDate((new \DateTimeImmutable())->modify('+' . rand(1, 15) . ' days'));
            $task->setCompleted($i % 3 === 0); // une tâche sur trois est complétée

            $categoryReference = match ($priority) {
                'low'    => 'category_3', // Loisir
                'medium' => 'category_0', // Personnel
                'high'   => 'category_1', // Travail
                'urgent' => 'category_2', // Urgent
            };

            $task->setCategory($this->getReference($categoryReference));

            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}