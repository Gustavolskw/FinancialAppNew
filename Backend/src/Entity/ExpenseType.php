<?php

namespace App\Entity;

use App\Repository\ExpenseTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseTypeRepository::class)]
class ExpenseType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isDefault = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'expenseType')]
    private Collection $typeExpense;

    public function __construct()
    {
        $this->typeExpense = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getTypeExpense(): Collection
    {
        return $this->typeExpense;
    }

    public function addTypeExpense(Expense $typeExpense): static
    {
        if (!$this->typeExpense->contains($typeExpense)) {
            $this->typeExpense->add($typeExpense);
            $typeExpense->setExpenseType($this);
        }

        return $this;
    }

    public function removeTypeExpense(Expense $typeExpense): static
    {
        if ($this->typeExpense->removeElement($typeExpense)) {
            // set the owning side to null (unless already changed)
            if ($typeExpense->getExpenseType() === $this) {
                $typeExpense->setExpenseType(null);
            }
        }

        return $this;
    }
}
