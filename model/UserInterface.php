<?php

declare(strict_types=1);

namespace model;

interface UserInterface
{
    public function connect(array $data): bool;

    public function disconnect(): bool;

    public function generateHiddenId(): string;
}