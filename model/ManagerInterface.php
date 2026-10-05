<?php

declare(strict_types=1);

namespace model;

use PDO;

interface ManagerInterface
{
    public function __construct(PDO $connect);
}