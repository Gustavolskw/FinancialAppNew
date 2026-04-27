<?php

namespace App\Infrastructure\Helper;

trait PasswordHashHelperTrait
{
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function passwordMatches(string $password, string $databasePassword): bool
    {
        return password_verify($password, $databasePassword);
    }
}
