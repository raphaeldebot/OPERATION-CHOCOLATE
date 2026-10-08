<?php

declare(strict_types=1);

namespace controller;

use Exception;
use PDO;
use model\manager\UserManager;

class UserController
{
private UserManager $userManager;



public function __construct(PDO $connect)
{
    $this->userManager = new UserManager($connect);
}
// Inscription
public function register(array $data): string
{
    if (!$this->validCsrf($data)) {
        return $this->json(false, 'Requête refusée', null, 403);
    }
    foreach ([
        'user_login',
        'user_email',
        'user_pwd',
        'user_pwd_confirm'
    ] as $field) {
        if (!isset($data[$field]) || !is_string($data[$field])) {
            return $this->json(false, 'Données invalides', null, 400);
        }
    }
    try {
        $user = $this->userManager->register($data);
        if ($user === false) {
            return $this->json(
                false,
                "Inscription impossible",
                null,
                500
            );
        }
        return $this->json(
            true,
            'Inscription réussie',
            ['user_id' => $user->getUserId()],
            201
        );
    } catch (Exception $e) {
        return $this->json(
            false,
            $e->getMessage(),
            null,
            400
        );
    }
}
// Connexion
public function login(array $data): string
{
    if (!$this->validCsrf($data)) {
        return $this->json(false, 'Requête refusée', null, 403);
    }
    if (
        !isset($data['user_login'], $data['user_pwd']) ||
        !is_string($data['user_login']) ||
        !is_string($data['user_pwd'])
    ) {
        return $this->json(false, 'Données invalides', null, 400);
    }
    if (!$this->userManager->connect($data)) {
        return $this->json(
            false,
            'Identifiants incorrects ou connexion temporairement bloquée',
            null,
            401
        );
    }
    return $this->json(
        true,
        'Connexion réussie',
        $this->currentUserData()
    );
}
// Déconnexion
public function logout(array $data): string
{
    if (!$this->validCsrf($data)) {
        return $this->json(false, 'Requête refusée', null, 403);
    }
    if (!$this->userManager->disconnect()) {
        return $this->json(
            false,
            'Déconnexion impossible',
            null,
            500
        );
    }
    return $this->json(
        true,
        'Déconnexion réussie'
    );
}
// État de connexion + token CSRF pour les formulaires
public function me(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $this->json(
        true,
        'État de connexion',
        [
            'user' => $this->currentUserData(),
            'csrf_token' => $_SESSION['csrf_token'],
        ]
    );
}
private function currentUserData(): ?array
{
    $user = $this->userManager->getCurrentUser();
    if ($user === null) {
        return null;
    }
    return [
        'user_id' => $user->getUserId(),
        'user_login' => $user->getUserLogin(),
        'user_email' => $user->getUserEmail(),
        'user_role' => $user->getUserRole(),
    ];
}
private function validCsrf(array $data): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
        && isset($_SESSION['csrf_token'])
        && is_string($data['_csrf'] ?? null)
        && hash_equals(
            $_SESSION['csrf_token'],
            $data['_csrf']
        );
}
private function json(
    bool $success,
    string $message,
    ?array $data = null,
    int $status = 200
): string {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_THROW_ON_ERROR);
}
}