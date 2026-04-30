<?php

namespace App\Infrastructure\DTO\Forms\User;

use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class UserAdminPostFormDto implements FormDtoInterface
{
    public readonly ?string $name;
    public readonly ?string $email;
    public readonly ?string $password;
    public readonly int $role;

    public function __construct(
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = RolesEnum::ADM->value();
    }
}
