<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->persist($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->remove($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Task[]
     */
    public function findAllOrderedByCreatedAt(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByStatus(bool $completed): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.completed = :completed')
            ->setParameter('completed', $completed)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findCompletedTasks(): array
    {
        return $this->findByStatus(true);
    }

    /**
     * @return Task[]
     */
    public function findPendingTasks(): array
    {
        return $this->findByStatus(false);
    }

    /**
     * @return Task[]
     */
    public function findOverdueTasks(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.completed = false')
            ->andWhere('t.dueDate < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.category = :category')
            ->setParameter('category', $category)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByPriority(string $priority): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.priority = :priority')
            ->setParameter('priority', $priority)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findTasksWithCategories(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->addSelect('c')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findTasksDueSoon(int $days = 7): array
    {
        $futureDate = new \DateTimeImmutable("+{$days} days");
        
        return $this->createQueryBuilder('t')
            ->andWhere('t.completed = false')
            ->andWhere('t.dueDate IS NOT NULL')
            ->andWhere('t.dueDate <= :futureDate')
            ->andWhere('t.dueDate >= :now')
            ->setParameter('futureDate', $futureDate)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(bool $completed): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.completed = :completed')
            ->setParameter('completed', $completed)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCompletedTasks(): int
    {
        return $this->countByStatus(true);
    }

    public function countPendingTasks(): int
    {
        return $this->countByStatus(false);
    }

    public function countOverdueTasks(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.completed = false')
            ->andWhere('t.dueDate < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTaskCountByPriority(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.priority, COUNT(t.id) as count')
            ->groupBy('t.priority')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['priority']] = (int) $row['count'];
        }

        return $counts;
    }

    public function getTaskCountByCategory(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('c.name as category_name, COUNT(t.id) as count')
            ->leftJoin('t.category', 'c')
            ->groupBy('c.name')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $categoryName = $row['category_name'] ?? 'Sans catégorie';
            $counts[$categoryName] = (int) $row['count'];
        }

        return $counts;
    }

    public function getStatisticsOptimized(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as total')
            ->addSelect('SUM(CASE WHEN t.completed = true THEN 1 ELSE 0 END) as completed')
            ->addSelect('SUM(CASE WHEN t.completed = false THEN 1 ELSE 0 END) as pending')
            ->addSelect('SUM(CASE WHEN t.completed = false AND t.dueDate < :now THEN 1 ELSE 0 END) as overdue')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleResult();

        return [
            'total' => (int) $result['total'],
            'completed' => (int) $result['completed'],
            'pending' => (int) $result['pending'],
            'overdue' => (int) $result['overdue'],
        ];
    }

    public function findTaskByTitle(string $title): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }
}