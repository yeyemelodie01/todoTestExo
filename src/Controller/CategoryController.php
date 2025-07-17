<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryService->getCategoriesWithTaskCounts();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->categoryService->createCategory(
                    $category->getName(),
                    $category->getColor(),
                    $category->getDescription()
                );

                $this->addFlash('success', 'Catégorie créée avec succès !');
                return $this->redirectToRoute('category_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->categoryService->updateCategory(
                    $category,
                    $category->getName(),
                    $category->getColor(),
                    $category->getDescription()
                );

                $this->addFlash('success', 'Catégorie modifiée avec succès !');
                return $this->redirectToRoute('category_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            try {
                $this->categoryService->deleteCategory($category);
                $this->addFlash('success', 'Catégorie supprimée !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('category_index');
    }
}