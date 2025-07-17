<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
    )]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: ['low', 'medium', 'high', 'urgent'])]
    private string $priority = 'medium';

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    // Setters
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;
        
        if ($completed && !$this->completedAt) {
            $this->completedAt = new \DateTimeImmutable();
        } elseif (!$completed) {
            $this->completedAt = null;
        }
        
        return $this;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): void
    {
        $this->completedAt = $completedAt;
    }



    // Business Methods
    public function markAsCompleted(): static
    {
        return $this->setCompleted(true);
    }

    public function markAsIncomplete(): static
    {
        return $this->setCompleted(false);
    }

    public function isOverdue(): bool
    {
        if (!$this->dueDate || $this->completed) {
            return false;
        }
        
        return $this->dueDate < new \DateTimeImmutable();
    }

    public function getDaysUntilDue(): ?int
    {
        if (!$this->dueDate) {
            return null;
        }
        
        $now = new \DateTimeImmutable();
        $diff = $this->dueDate->diff($now);
        
        return $diff->invert ? $diff->days : -$diff->days;
    }

    public function getPriorityWeight(): int
    {
        return match($this->priority) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'urgent' => 4,
            default => 2
        };
    }
}