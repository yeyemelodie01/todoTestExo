<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private string $name;

    #[ORM\Column(type: 'string', length: 7)]
    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'La couleur doit être un code hexadécimal valide (ex: #FF0000)'
    )]
    private string $color = '#007bff';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Task::class)]
    private Collection $tasks;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->tasks = new ArrayCollection();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setTasks(Collection $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    // Setters
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    // Business Methods
    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setCategory($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getCategory() === $this) {
                $task->setCategory(null);
            }
        }

        return $this;
    }

    public function getTaskCount(): int
    {
        return $this->tasks->count();
    }

    public function getCompletedTaskCount(): int
    {
        return $this->tasks->filter(fn(Task $task) => $task->isCompleted())->count();
    }

    public function getCompletionRate(): float
    {
        $total = $this->getTaskCount();
        
        if ($total === 0) {
            return 0.0;
        }
        
        return ($this->getCompletedTaskCount() / $total) * 100;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}