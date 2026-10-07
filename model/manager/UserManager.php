<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use PDOException;
use model\ManagerInterface;
use model\UserInterface;
use model\mapping\UserMapping;

class UserManager implements ManagerInterface, UserInterface
{
    private PDO $db;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_BLOCK_SECONDS = 600;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    public function register(array $data): UserMapping|false
    {
        if (
            !isset(
                $data['user_login'],
                $data['user_email'],
                $data['user_pwd'],
                $data['user_pwd_confirm']
            )
        ) {
            throw new Exception("Tous les champs sont obligatoires");
        }

        if ($data['user_pwd'] !== $data['user_pwd_confirm']) {
            throw new Exception("Les mots de passe ne correspondent pas");
        }

        $this->validatePassword($data['user_pwd']);

        if (
            $this->userExists(
                $data['user_login'],
                $data['user_email']
            )
        ) {
            throw new Exception(
                "Ce nom d'utilisateur ou cet email est déjà utilisé"
            );
        }

        $user = new UserMapping([
            'user_login' => $data['user_login'],
            'user_email' => $data['user_email'],
            'user_pwd' => password_hash(
                $data['user_pwd'],
                PASSWORD_DEFAULT
            ),
            'user_role' => 'user',
        ]);

        $sql = "
            INSERT INTO user (
                user_login,
                user_email,
                user_pwd,
                user_role
            )
            VALUES (?, ?, ?, ?)
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $user->getUserLogin(),
                $user->getUserEmail(),
                $user->getUserPwd(),
                $user->getUserRole(),
            ]);

            return $this->getUserById(
                (int) $this->db->lastInsertId()
            ) ?? false;

        } catch (PDOException $e) {
            error_log($e->getMessage());

            if ($e->getCode() === '23000') {
                throw new Exception(
                    "Ce nom d'utilisateur ou cet email est déjà utilisé"
                );
            }

            return false;
        }
    }

    public function connect(array $data): bool
    {
        if (
            !isset(
                $data['user_login'],
                $data['user_pwd']
            )
        ) {
            return false;
        }

        if ($this->isLoginBlocked()) {
            return false;
        }

        $sql = "
            SELECT *
            FROM user
            WHERE user_login = ?
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                trim((string) $data['user_login'])
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (
                !$result ||
                !password_verify(
                    (string) $data['user_pwd'],
                    $result['user_pwd']
                )
            ) {
                $this->registerLoginFailure();
                return false;
            }

            session_regenerate_id(true);

            $_SESSION['user'] = [
                'user_id' => (int) $result['user_id'],
                'user_email' => $result['user_email'],
                'user_login' => $result['user_login'],
                'user_role' => $result['user_role'],
            ];

            $this->resetLoginAttempts();

            return true;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function disconnect(): bool
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        return session_destroy();
    }

    public function getCurrentUser(): ?UserMapping
    {
        if (!isset($_SESSION['user']['user_id'])) {
            return null;
        }

        return new UserMapping($_SESSION['user']);
    }

    public function getUserById(int $userId): ?UserMapping
    {
        if ($userId <= 0) {
            return null;
        }

        $sql = "
            SELECT
                user_id,
                user_email,
                user_login,
                created_at,
                user_role
            FROM user
            WHERE user_id = ?
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $result
                ? new UserMapping($result)
                : null;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getUserByLogin(string $login): ?UserMapping
    {
        $sql = "
            SELECT
                user_id,
                user_email,
                user_login,
                created_at,
                user_role
            FROM user
            WHERE user_login = ?
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([trim($login)]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $result
                ? new UserMapping($result)
                : null;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function userExists(
        string $login,
        string $email
    ): bool {

        $sql = "
            SELECT user_id
            FROM user
            WHERE user_login = ?
               OR user_email = ?
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                trim($login),
                trim($email),
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $result !== false;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new Exception(
                "Le mot de passe doit contenir au moins 8 caractères"
            );
        }

        if (
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            throw new Exception(
                "Le mot de passe doit contenir une majuscule, une minuscule et un chiffre"
            );
        }
    }

    private function isLoginBlocked(): bool
    {
        if (!isset($_SESSION['_login_attempts'])) {
            return false;
        }

        $attempts = $_SESSION['_login_attempts'];

        if (
            time() - $attempts['first_attempt']
            > self::LOGIN_BLOCK_SECONDS
        ) {
            $this->resetLoginAttempts();
            return false;
        }

        return $attempts['count']
            >= self::MAX_LOGIN_ATTEMPTS;
    }

    private function registerLoginFailure(): void
    {
        if (!isset($_SESSION['_login_attempts'])) {
            $_SESSION['_login_attempts'] = [
                'count' => 1,
                'first_attempt' => time(),
            ];

            return;
        }

        $_SESSION['_login_attempts']['count']++;
    }

    private function resetLoginAttempts(): void
    {
        unset($_SESSION['_login_attempts']);
    }
}