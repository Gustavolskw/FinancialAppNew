<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'transactionExpense', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transaction $expenseTransaction = null;

    #[ORM\ManyToOne(inversedBy: 'typeExpense')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExpenseType $expenseType = null;

    #[ORM\ManyToOne(inversedBy: 'paymentMethodExpense')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PaymentMethod $expensePaymentMethod = null;

    #[ORM\Column]
    private ?int $installments = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getExpenseTransaction(): ?Transaction
    {
        return $this->expenseTransaction;
    }

    public function setExpenseTransaction(Transaction $expenseTransaction): static
    {
        $this->expenseTransaction = $expenseTransaction;

        return $this;
    }

    public function getExpenseType(): ?ExpenseType
    {
        return $this->expenseType;
    }

    public function setExpenseType(?ExpenseType $expenseType): static
    {
        $this->expenseType = $expenseType;

        return $this;
    }

    public function getExpensePaymentMethod(): ?PaymentMethod
    {
        return $this->expensePaymentMethod;
    }

    public function setExpensePaymentMethod(?PaymentMethod $expensePaymentMethod): static
    {
        $this->expensePaymentMethod = $expensePaymentMethod;

        return $this;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function setInstallments(int $installments): static
    {
        $this->installments = $installments;

        return $this;
    }
}
