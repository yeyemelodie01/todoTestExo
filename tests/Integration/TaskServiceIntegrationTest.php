<?php

namespace App\Tests\Integration;

use App\Entity\Task;
use App\Entity\Category;
use App\Service\TaskService;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test d'intégration du TaskService
 *
 * Ce test vérifie que le service fonctionne correctement
 * avec la vraie base de données et les vrais repositories
 */
class TaskServiceIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TaskService $taskService;
    private CategoryService $categoryService;

    /**
     * Setup : Préparation avant chaque test
     * On démarre le kernel Symfony et on récupère nos services
     */
    protected function setUp(): void
    {
        // 1. Démarrer le kernel Symfony (lance l'application en mode test)
        $kernel = self::bootKernel();

        // 2. Récupérer le container de services
        $container = $kernel->getContainer();

        // 3. Récupérer nos services depuis le container
        $this->entityManager = $container
            ->get('doctrine')
            ->getManager();

        $this->taskService = $container->get(TaskService::class);
        $this->categoryService = $container->get(CategoryService::class);
    }

    /**
     * Nettoyage après chaque test
     * Important pour l'isolation des tests !
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Fermer l'EntityManager pour éviter les fuites mémoire
        $this->entityManager->close();
        $this->entityManager = null;
    }

    // 🎯 ICI VOUS ALLEZ ÉCRIRE VOS TESTS
    /**
     * Test : Créer une tâche simple et vérifier qu'elle est sauvegardée
     */
    public function testCreateSimpleTask(): void
    {
        // 🎯 ARRANGE : Préparer les données
        $title = 'Ma première tâche de test';
        $description = 'Description de test pour vérifier la persistance';

        // 🎯 ACT : Exécuter l'action à tester
        $task = $this->taskService->createTask($title, $description);

        // 🎯 ASSERT : Vérifier les résultats

        // 1. Vérifier que la tâche a un ID (donc sauvegardée en base)
        $this->assertNotNull($task->getId(), 'La tâche devrait avoir un ID après sauvegarde');

        // 2. Vérifier que les données sont correctes
        $this->assertEquals($title, $task->getTitle(), 'Le titre devrait être celui fourni');
        $this->assertEquals($description, $task->getDescription(), 'La description devrait être celle fournie');

        // 3. Vérifier les valeurs par défaut
        $this->assertFalse($task->isCompleted(), 'Une nouvelle tâche devrait être non terminée');
        $this->assertEquals('medium', $task->getPriority(), 'La priorité par défaut devrait être "medium"');
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt(), 'CreatedAt devrait être défini');

        // 🔍 VÉRIFICATION EN BASE : Le plus important !
        // On va rechercher la tâche directement en base pour être sûr qu'elle y est
        $taskFromDatabase = $this->entityManager
            ->getRepository(Task::class)
            ->find($task->getId());

        $this->assertNotNull($taskFromDatabase, 'La tâche devrait être trouvée en base de données');
        $this->assertEquals($title, $taskFromDatabase->getTitle(), 'Le titre en base devrait correspondre');
    }

    /**
     * Test : Créer une tâche avec une catégorie (test des relations)
     */
    public function testCreateTaskWithCategory(): void
    {
        // 🎯 ARRANGE : Créer d'abord une catégorie
        $category = $this->categoryService->createCategory(
            'Travail',
            '#FF0000',
            'Catégorie pour les tâches de travail'
        );

        // 🎯 ACT : Créer une tâche avec cette catégorie
        $task = $this->taskService->createTask(
            'Tâche avec catégorie',
            'Test des relations',
            $category
        );

        // 🎯 ASSERT : Vérifications

        // 1. Vérifier que la relation est bien définie
        $this->assertSame($category, $task->getCategory(), 'La catégorie devrait être liée à la tâche');

        // 2. Vérifier en base de données avec les relations chargées
        $taskFromDb = $this->entityManager
            ->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->addSelect('c')
            ->where('t.id = :id')
            ->setParameter('id', $task->getId())
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNotNull($taskFromDb, 'La tâche devrait être en base');
        $this->assertNotNull($taskFromDb->getCategory(), 'La catégorie devrait être chargée');
        $this->assertEquals('Travail', $taskFromDb->getCategory()->getName(), 'Le nom de catégorie devrait correspondre');

        // 3. Vérifier l'inverse de la relation
        $categoryFromDb = $this->entityManager
            ->getRepository(Category::class)
            ->find($category->getId());

        $this->assertCount(1, $categoryFromDb->getTasks(), 'La catégorie devrait avoir 1 tâche');
        $this->assertSame($taskFromDb, $categoryFromDb->getTasks()->first(), 'La première tâche devrait être la nôtre');
    }

    /**
     * Test : Vérifier que les erreurs sont bien gérées
     */
    public function testCreateTaskWithInvalidData(): void
    {
        // 🎯 Test : Titre trop court
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre doit contenir au moins 3 caractères');

        // Cette action devrait lever une exception
        $this->taskService->createTaskWithValidation('AB', 'Description valide');
    }


}
