<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"project_list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     *
     * @Groups({"project_list"})
     *
     * @Assert\Type(type="string", message="Le nom doit être du texte")
     * @Assert\NotNull(message="Veuillez renseigner un nom")
     * @Assert\NotBlank(message="Le nom ne doit pas être vide")
     * @Assert\Length(
     *     min=2,
     *     minMessage="Le titre doit faire plus de deux caractères"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"project_list"})
     *
     * @Assert\Type(type="integer", message="La priorité doit être un entier")
     * @Assert\NotNull(message="Veuillez renseigner la priorité")
     * @Assert\NotBlank(message="La priorité ne doit pas être vide")
     */
    private $priority;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $doneAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"project_list"})
     *
     * @Assert\Type(type="string", message="Le détail doit être du texte")
     */
    private $explanation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"project_list"})
     *
     * @Assert\Type(type="object", message="La catégorie doit être une instance de Category")
     */
    private $category;

    use Hydrate;

    /**
     * @return string
     * @Groups({"project_list"})
     */
    public function getRawCreatedAt()
    {
        return $this->getCreatedAt()->format('j/m/Y');
    }

    /**
     * @return string
     * @Groups({"project_list"})
     */
    public function getRawDoneAt()
    {
        if(!is_null($this->getDoneAt())) {
            return $this->getDoneAt()->format('j/m/Y');
        } return null;
    }

    /**
     * @return string
     * @Groups({"project_list"})
     */
    public function getDuration(){
        if(!is_null($this->getDoneAt())){
            return $this->getCreatedAt()
                ->diff(
                    $this->getDoneAt(),
                    true
                )
                ->format('%a');
        } else{
            return null;
        }
    }

    /**
     * @return string
     * @Groups({"project_list"})
     */
    public function getSince(){
        return $this->getCreatedAt()
            ->diff(
                new \DateTime(),
                true
            )
            ->format('%a');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDoneAt(): ?\DateTimeInterface
    {
        return $this->doneAt;
    }

    public function setDoneAt(?\DateTimeInterface $doneAt): self
    {
        $this->doneAt = $doneAt;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): self
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
