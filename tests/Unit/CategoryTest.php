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

        $this->expectException(\InvalidArgumentException::class);
        $category->setName('');

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

    // Gestion de la liste de tâches
    // addTask($task)
    public function testAddTask(){
        $category = new Category();
        $category->setName('Test Category');

        $task = new Task();
        $task->setTitle('New Task');

        $category->addTask($task);

        $this->assertCount(1, $category->getTasks());
        $this->assertTrue($category->getTasks()->contains($task));
    }

    // removeTask($task)
    public function testRemoveTask(){
        $category = new Category();
        $category->setName('Test Category');

        $task = new Task();
        $task->setTitle('Task to Remove');

        $category->addTask($task);
        $category->removeTask($task);

        $this->assertCount(0, $category->getTasks());
        $this->assertFalse($category->getTasks()->contains($task));
    }

    // Tâches en double appeler deux fois addTask($sameTask)
    public function testAddDuplicateTask(){
        $category = new Category();
        $category->setName('Test Category');

        $task = new Task();
        $task->setTitle('Unique Task');

        $category->addTask($task);
        $category->addTask($task);

        $this->assertCount(1, $category->getTasks());
    }

    // Requête de tâches filtrées(si votre Category propose un helper)
    // exemple : $category->getTasksByStatus('completed');

}