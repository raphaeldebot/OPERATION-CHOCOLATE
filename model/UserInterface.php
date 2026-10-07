<?php

declare(strict_types=1);

namespace model;

use model\mapping\UserMapping;

interface UserInterface
{
    public function register(array $data): UserMapping|false;
    public function connect(array $data): bool;
    public function disconnect(): bool;
    public function getCurrentUser(): ?UserMapping;
}