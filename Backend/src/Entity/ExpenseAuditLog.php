<?php

namespace App\Entity;

use App\Repository\ExpenseAuditLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseAuditLogRepository::class)]
class ExpenseAuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $originalExpenseId;

    #[ORM\Column]
    private int $originalTransactionId;

    #[ORM\Column]
    private int $expenseTypeId;

    #[ORM\Column]
    private int $paymentMethodId;

    #[ORM\Column]
    private int $installments;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(length: 255)]
    private string $location;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $date;

    #[ORM\Column]
    private int $month;

    #[ORM\Column]
    private int $year;

    #[ORM\Column]
    private int $walletId;

    #[ORM\Column]
    private int $deletedByUserId;

    #[ORM\Column(length: 255)]
    private string $deletedByUserName;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $deletedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalExpenseId(): int
    {
        return $this->originalExpenseId;
    }

    public function setOriginalExpenseId(int $v): static
    {
        $this->originalExpenseId = $v;
        return $this;
    }

    public function getOriginalTransactionId(): int
    {
        return $this->originalTransactionId;
    }

    public function setOriginalTransactionId(int $v): static
    {
        $this->originalTransactionId = $v;
        return $this;
    }

    public function getExpenseTypeId(): int
    {
        return $this->expenseTypeId;
    }

    public function setExpenseTypeId(int $v): static
    {
        $this->expenseTypeId = $v;
        return $this;
    }

    public function getPaymentMethodId(): int
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(int $v): static
    {
        $this->paymentMethodId = $v;
        return $this;
    }

    public function getInstallments(): int
    {
        return $this->installments;
    }

    public function setInstallments(int $v): static
    {
        $this->installments = $v;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $v): static
    {
        $this->amount = $v;
        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $v): static
    {
        $this->location = $v;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $v): static
    {
        $this->description = $v;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $v): static
    {
        $this->date = $v;
        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $v): static
    {
        $this->month = $v;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $v): static
    {
        $this->year = $v;
        return $this;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function setWalletId(int $v): static
    {
        $this->walletId = $v;
        return $this;
    }

    public function getDeletedByUserId(): int
    {
        return $this->deletedByUserId;
    }

    public function setDeletedByUserId(int $v): static
    {
        $this->deletedByUserId = $v;
        return $this;
    }

    public function getDeletedByUserName(): string
    {
        return $this->deletedByUserName;
    }

    public function setDeletedByUserName(string $v): static
    {
        $this->deletedByUserName = $v;
        return $this;
    }

    public function getDeletedAt(): \DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTime $v): static
    {
        $this->deletedAt = $v;
        return $this;
    }
}
