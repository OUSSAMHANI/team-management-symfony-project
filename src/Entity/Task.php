<?php
// src/Entity/Task.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\TaskRepository")]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 20)]
    private string $status;

    // The cascade remove is removed, and the assignedTo field is now optional
    #[ORM\ManyToOne(targetEntity: "App\Entity\Project", inversedBy: "tasks")]
    #[ORM\JoinColumn(nullable: false)]  // Project is still mandatory
    private Project $project;

    #[ORM\ManyToOne(targetEntity: "App\Entity\TeamMember", inversedBy: "tasks")]
    #[ORM\JoinColumn(nullable: true)]  // AssignedTo is now optional
    private ?TeamMember $assignedTo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getAssignedTo(): ?TeamMember
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?TeamMember $assignedTo): self
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }
}
