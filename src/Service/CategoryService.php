<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    public function createCategory(string $name, string $color = '#007bff', ?string $description = null): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setColor($color);
        
        if ($description) {
            $category->setDescription($description);
        }

        $this->validateCategory($category);
        $this->categoryRepository->save($category, true);

        return $category;
    }

    public function updateCategory(Category $category, string $name, string $color, ?string $description = null): Category
    {
        $category->setName($name);
        $category->setColor($color);
        $category->setDescription($description);

        $this->validateCategory($category);
        $this->categoryRepository->save($category, true);

        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        $this->categoryRepository->remove($category, true);
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAllOrderedByName();
    }

    public function getCategoriesWithTasks(): array
    {
        return $this->categoryRepository->findCategoriesWithTasks();
    }

    public function getCategoriesWithTaskCounts(): array
    {
        return $this->categoryRepository->getCategoriesWithTaskCounts();
    }

    public function findCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function findCategoryByName(string $name): ?Category
    {
        return $this->categoryRepository->findByName($name);
    }

    private function validateCategory(Category $category): void
    {
        $violations = $this->validator->validate($category);
        
        if (count($violations) > 0) {
            throw new \InvalidArgumentException(
                'Validation failed: ' . (string) $violations
            );
        }
    }
}