<?php
// src/Entity/TeamMember.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\TeamMemberRepository")]
class TeamMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $name;

    #[ORM\Column(type: "string", length: 100)]
    private $role;

    // A team member is related to the team, no need for cascade here
    #[ORM\ManyToOne(targetEntity: "App\Entity\Team", inversedBy: "members")]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    // Cascade delete related tasks when the team member is deleted
    #[ORM\OneToMany(targetEntity: "App\Entity\Task", mappedBy: "assignedTo")]
    private $tasks;

    // New property for storing the image filename
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $image;

    public function __construct()
    {
        // Initialize the tasks collection
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setAssignedTo($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // Cascade remove is already applied, no need for any other action here
        }

        return $this;
    }

    // Getter and setter for the image property

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
