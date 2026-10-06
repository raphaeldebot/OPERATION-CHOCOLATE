<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class UserMapping extends AbstractMapping
{
    protected ?int $user_id = null;
    protected ?string $user_email = null;
    protected ?string $user_login = null;
    protected ?string $user_pwd = null;
    protected ?string $created_at = null;
    protected ?string $user_role = null;


// getter user_id 
public function getUserId(): ?int
{
    return $this->user_id;
}

// setter user_id 

public function setUserId(?int $user_id): void
{
    if ($user_id === null) {
        return;
    }

    if ($user_id <= 0) {
        throw new Exception("user_id doit être un entier positif");
    }

    $this->user_id = $user_id;
}

// getter user_email 
public function getUserEmail(): ?string
{
    return $this->user_email;
}

// setter user_email 

public function setUserEmail(string $user_email): void
{
    $user_email = trim($user_email);

    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("L'adresse email n'est pas valide");
    }

    if (mb_strlen($user_email) > 45) {
        throw new Exception("L'adresse email ne peut pas dépasser 45 caractères");
    }

    $this->user_email = $user_email;
}

// getter user_login 
public function getUserLogin(): ?string
{
    return $this->user_login;
}

// setter user_login 
public function setUserLogin(string $user_login): void
{
    $user_login = trim($user_login);

    if (mb_strlen($user_login) < 3 || mb_strlen($user_login) > 45) {
        throw new Exception("Le nom d'utilisateur doit avoir entre 3 et 45 caractères");
    }

    $this->user_login = $user_login;
}

// getter user_pwd
public function getUserPwd(): ?string
{
    return $this->user_pwd;
}

// setter user_pwd
public function setUserPwd(string $user_pwd): void
{
    $this->user_pwd = trim($user_pwd);
}

}