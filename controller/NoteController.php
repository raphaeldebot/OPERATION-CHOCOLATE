<?php

declare(strict_types=1);

namespace controller;

use Exception;
use PDO;
use model\manager\NoteManager;

class NoteController
{
private NoteManager $noteManager;



public function __construct(PDO $connect)
{
    $this->noteManager = new NoteManager($connect);
}
// Ajouter ou modifier une note
public function rate(array $data): string
{
    // Utilisateur connecté obligatoire
    $userId = $_SESSION['user']['user_id'] ?? null;
    if (!is_int($userId) || $userId <= 0) {
        return $this->json(
            false,
            'Connexion requise',
            null,
            401
        );
    }
    // Protection CSRF
    if (!$this->validCsrf($data)) {
        return $this->json(
            false,
            'Requête refusée',
            null,
            403
        );
    }
    // Validation des données
    $recipeId = filter_var(
        $data['recipe_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $value = filter_var(
        $data['note_value'] ?? null,
        FILTER_VALIDATE_INT
    );
    if (
        !is_int($recipeId) ||
        $recipeId <= 0 ||
        !is_int($value) ||
        $value < 1 ||
        $value > 5
    ) {
        return $this->json(
            false,
            'Données invalides',
            null,
            400
        );
    }
    try {
        // Enregistrement via NoteManager
        $result = $this->noteManager->rateRecipe(
            $recipeId,
            $userId,
            $value
        );
        if (
            $result === null ||
            $result['note'] === null ||
            $result['average_rating'] === null ||
            $result['vote_count'] < 1
        ) {
            return $this->json(
                false,
                'Impossible d’enregistrer la note',
                null,
                500
            );
        }
        return $this->json(
            true,
            'Note enregistrée',
            [
                'note' => $result['note']->getNoteValue(),
                'average_rating' => $result['average_rating'],
                'vote_count' => $result['vote_count'],
            ]
        );
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->json(
            false,
            'Impossible d’enregistrer la note',
            null,
            500
        );
    }
}
    // Récupérer la note personnelle de l'utilisateur connecté
    public function mine(int $recipeId): string
    {
        $userId = $_SESSION['user']['user_id'] ?? null;

        // Connexion obligatoire
        if (!is_int($userId) || $userId <= 0) {
            return $this->json(
                false, 'Connexion requise', null, 401
            );
        }

        // Vérifier l'identifiant de la recette
        if ($recipeId <= 0) {
            return $this->json(
                false, 'Recette invalide', null, 400
            );
        }

        try {
            // Récupérer la note enregistrée en base
            $note = $this->noteManager->getUserRating(
                $recipeId,
                $userId
            );

            return $this->json(
                true,
                'Note récupérée',
                ['note' => $note?->getNoteValue()]
            );

        } catch (\Throwable $e) {
            error_log($e->getMessage());

            return $this->json(
                false,
                'Impossible de récupérer la note',
                null,
                500
            );
        }
    }
// Vérification CSRF
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
// Réponse JSON
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
    ], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
}
}