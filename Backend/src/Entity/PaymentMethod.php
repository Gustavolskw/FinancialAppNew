<?php

namespace App\Entity;

use App\Repository\PaymentMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'expensePaymentMethod')]
    private Collection $paymentMethodExpense;

    public function __construct()
    {
        $this->paymentMethodExpense = new ArrayCollection();
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

    /**
     * @return Collection<int, Expense>
     */
    public function getPaymentMethodExpense(): Collection
    {
        return $this->paymentMethodExpense;
    }

    public function addPaymentMethodExpense(Expense $paymentMethodExpense): static
    {
        if (!$this->paymentMethodExpense->contains($paymentMethodExpense)) {
            $this->paymentMethodExpense->add($paymentMethodExpense);
            $paymentMethodExpense->setExpensePaymentMethod($this);
        }

        return $this;
    }

    public function removePaymentMethodExpense(Expense $paymentMethodExpense): static
    {
        if ($this->paymentMethodExpense->removeElement($paymentMethodExpense)) {
            // set the owning side to null (unless already changed)
            if ($paymentMethodExpense->getExpensePaymentMethod() === $this) {
                $paymentMethodExpense->setExpensePaymentMethod(null);
            }
        }

        return $this;
    }
}
