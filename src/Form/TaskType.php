<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Entrez le titre de la tâche',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée (optionnel)',
                    'class' => 'form-control',
                    'rows' => 4
                ]
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Faible' => 'low',
                    'Moyenne' => 'medium',
                    'Élevée' => 'high',
                    'Urgente' => 'urgent'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('dueDate', DateTimeType::class, [
                'label' => 'Date limite',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sélectionner une catégorie',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}