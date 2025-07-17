<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORIES = [
        ['name' => 'Personnel', 'color' => '#FF5733', 'description' => 'Tâches personnelles et quotidiennes'],
        ['name' => 'Travail',    'color' => '#007BFF', 'description' => 'Tâches liées au travail'],
        ['name' => 'Urgent',     'color' => '#DC3545', 'description' => 'Tâches urgentes à traiter rapidement'],
        ['name' => 'Loisir',     'color' => '#28A745', 'description' => 'Activités de loisir et détente'],
        ['name' => 'Courses',    'color' => '#FFC107', 'description' => 'Liste de courses et achats à faire'],
        ['name' => 'Santé',      'color' => '#6F42C1', 'description' => 'Rendez-vous médicaux et santé']
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $index => $data) {
            $category = (new Category())
                ->setName($data['name'])
                ->setColor($data['color'])
                ->setDescription($data['description']);

            $manager->persist($category);

            // Référence pour les autres fixtures (comme TaskFixtures)
            $this->addReference('category_' . $index, $category);
        }

        $manager->flush();
    }
}