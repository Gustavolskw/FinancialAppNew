<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $month = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\OneToOne(mappedBy: 'expenseTransaction', cascade: ['persist', 'remove'])]
    private ?Expense $transactionExpense = null;

    #[ORM\OneToOne(mappedBy: 'transaction', cascade: ['persist', 'remove'])]
    private ?Entry $entryTransaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getTransactionExpense(): ?Expense
    {
        return $this->transactionExpense;
    }

    public function setTransactionExpense(Expense $transactionExpense): static
    {
        // set the owning side of the relation if necessary
        if ($transactionExpense->getExpenseTransaction() !== $this) {
            $transactionExpense->setExpenseTransaction($this);
        }

        $this->transactionExpense = $transactionExpense;

        return $this;
    }

    public function getEntryTransaction(): ?Entry
    {
        return $this->entryTransaction;
    }

    public function setEntryTransaction(Entry $entryTransaction): static
    {
        // set the owning side of the relation if necessary
        if ($entryTransaction->getTransaction() !== $this) {
            $entryTransaction->setTransaction($this);
        }

        $this->entryTransaction = $entryTransaction;

        return $this;
    }
}
