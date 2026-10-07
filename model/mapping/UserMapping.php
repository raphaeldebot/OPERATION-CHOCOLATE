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
public function setUserPwd(?string $user_pwd): void
    {
        $user_pwd = trim((string) $user_pwd);
 
        if (empty($user_pwd)) {
            throw new Exception("Mot de passe non valide");
        }
 
        // VARCHAR(255) dans la base (compatible avec un hash password_hash)
        if (strlen($user_pwd) > 255) {
            throw new Exception("Le mot de passe ne peut pas dépasser 255 caractères");
        }
 
        $this->user_pwd = $user_pwd;
}

// getter created_at
public function getCreatedAt(): ?string
{
    return $this->created_at;
}

// setter created_at
public function setCreatedAt(?string $created_at): void
    {
        if (is_null($created_at)) {
            return;
        }
 
        $timestamp = strtotime($created_at);

        if ($timestamp === false) {
        throw new Exception("La date d'inscription n'est pas au bon format");
        }

        $this->created_at = date('Y-m-d H:i:s', $timestamp);
    }

// getter user_role
public function getUserRole(): ?string
{
    return $this->user_role;
}

// setter user_role
public function setUserRole(?string $user_role): void
    {
        if (is_null($user_role)) {
            return;
        }
 
        // ENUM('user', 'admin') dans la base
        if (!in_array($user_role, ['user', 'admin'], true)) {
            throw new Exception("Le rôle de l'utilisateur doit être 'user' ou 'admin'");
        }
 
        $this->user_role = $user_role;
    }

    
}