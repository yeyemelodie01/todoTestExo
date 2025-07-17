<?php

use App\Entity\Category;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    // Création d'une catégorie
    public function testAddCategory(){
        $category = new Category();

        $category->setName('Test Category')
                 ->setColor('#FFFFFF')
                 ->setDescription('This is a test category')
                 ->setCreatedAt(new \DateTimeImmutable());

        $this->assertInstanceOf(Category::class, $category);
    }

    // Setter / Getter du nom
    public function testSetGetName(){
        $category = new Category();
        $category->setName('Test Category');

        $this->assertEquals('Test Category', $category->getName());
    }

    //Rejet d'un nom vide ou trop long
    public function testSetNameEmptyOrTooLong(){
        $category = new Category();

        // Test nom vide
        $this->expectException(\InvalidArgumentException::class);
        $category->setName('');

        // Test nom trop long
        $this->expectException(\InvalidArgumentException::class);
        $category->setName(str_repeat('a', 256));
    }

    //Slug automatique
    public function testSlugGeneration(){
        $category = new Category();
        $category->setName('Test Category');

        // Assuming the slug is generated automatically
        $this->assertEquals('test-category', $category->getSlug());
    }

    // Gestion de la liste des tâches
    public function testTaskManagement(){
        $category = new Category();
        $category->setName('Test Category');

        // Ajout de tâches
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task2 = new Task();
        $task2->setTitle('Task 2');

        $category->getTasks()->add($task1);
        $category->getTasks()->add($task2);

        $this->assertCount(2, $category->getTasks());
    }
    // Gestion de la liste de tâches
    public function testRemoveTask(){
        $category = new Category();
        $category->setName('Test Category');

        $task1 = new Task();
        $task1->setTitle('Task 1');
        $category->getTasks()->add($task1);

        // Suppression de la tâche
        $category->getTasks()->removeElement($task1);

        $this->assertCount(0, $category->getTasks());
    }

    // Tâches en double appeler deux fois addTask($sameTask)
    public function testAddDuplicateTask(){
        $category = new Category();
        $category->setName('Test Category');

        $task = new Task();
        $task->setTitle('Unique Task');

        // Ajout de la même tâche deux fois
        $category->addTask($task);
        $category->addTask($task);

        // Vérification que la tâche n'est pas dupliquée
        $this->assertCount(1, $category->getTasks());
    }

    // Requête de tâches filtrées(si votre Category propose un helper)
    // exemple : $category->getTasksByStatus('completed');
    public function testGetTasksByStatus(){
        $category = new Category();
        $category->setName('Test Category');

        $task1 = new Task();
        $task1->setTitle('Task 1')->setStatus('completed');
        $task2 = new Task();
        $task2->setTitle('Task 2')->setStatus('pending');

        $category->getTasks()->add($task1);
        $category->getTasks()->add($task2);

        // Assuming getTasksByStatus is a method that filters tasks by status
        $completedTasks = $category->getTasksByStatus('completed');

        $this->assertCount(1, $completedTasks);
        $this->assertEquals('Task 1', $completedTasks[0]->getTitle());
    }
}