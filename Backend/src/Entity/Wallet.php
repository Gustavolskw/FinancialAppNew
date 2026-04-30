<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToOne(inversedBy: 'userWallet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $walletUser = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'transactionWallet', orphanRemoval: true)]
    private Collection $walletTransactions;

    public function __construct()
    {
        $this->walletTransactions = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getWalletUser(): ?User
    {
        return $this->walletUser;
    }

    public function setWalletUser(User $walletUser): static
    {
        $this->walletUser = $walletUser;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getWalletTransactions(): Collection
    {
        return $this->walletTransactions;
    }

    public function addWalletTransaction(Transaction $walletTransaction): static
    {
        if (!$this->walletTransactions->contains($walletTransaction)) {
            $this->walletTransactions->add($walletTransaction);
            $walletTransaction->setTransactionWallet($this);
        }

        return $this;
    }

    public function removeWalletTransaction(Transaction $walletTransaction): static
    {
        if ($this->walletTransactions->removeElement($walletTransaction)) {
            // set the owning side to null (unless already changed)
            if ($walletTransaction->getTransactionWallet() === $this) {
                $walletTransaction->setTransactionWallet(null);
            }
        }

        return $this;
    }
}
