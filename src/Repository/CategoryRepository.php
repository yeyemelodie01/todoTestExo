<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $category, bool $flush = false): void
    {
        $this->getEntityManager()->persist($category);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $category, bool $flush = false): void
    {
        $this->getEntityManager()->remove($category);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Category[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Category[]
     */
    public function findCategoriesWithTasks(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.tasks', 't')
            ->addSelect('t')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCategoriesWithTaskCounts(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.name, c.color, COUNT(t.id) as task_count')
            ->leftJoin('c.tasks', 't')
            ->groupBy('c.id, c.name, c.color')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}