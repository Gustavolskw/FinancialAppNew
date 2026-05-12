<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

final class DummyEntity
{
    private ?int $id = null;
    private ?string $name = null;
    private ?bool $status = null;
    private ?\DateTimeImmutable $updatedAt = null;
    private ?self $related = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRelated(): ?self
    {
        return $this->related;
    }

    public function setRelated(?self $related): self
    {
        $this->related = $related;

        return $this;
    }
}
