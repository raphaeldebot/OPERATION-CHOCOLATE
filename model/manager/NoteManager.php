<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use PDOException;
use model\ManagerInterface;
use model\mapping\NoteMapping;

class NoteManager implements ManagerInterface
{
    private PDO $db;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Ajouter une note ou remplacer l'ancienne
    public function rateRecipe(
        int $recipeId,
        int $userId,
        int $value
    ): ?array {

        // Validation via NoteMapping
        $note = new NoteMapping([
            'recipe_id' => $recipeId,
            'user_id' => $userId,
            'note_value' => $value,
        ]);

        $sql = "
            INSERT INTO note (
                recipe_id,
                user_id,
                note_value
            )
            VALUES (?, ?, ?)

            ON DUPLICATE KEY UPDATE
                note_value = VALUES(note_value),
                note_date = CURRENT_TIMESTAMP
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $note->getRecipeId(),
                $note->getUserId(),
                $note->getNoteValue(),
            ]);

            $userRating = $this->getUserRating(
                $recipeId,
                $userId
            );

            $stats = $this->getRatingStats($recipeId);

            return [
                'note' => $userRating,
                'average_rating' => $stats['average_rating'],
                'vote_count' => $stats['vote_count'],
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Note donnée par un utilisateur à une recette
    public function getUserRating(
        int $recipeId,
        int $userId
    ): ?NoteMapping {

        if ($recipeId <= 0 || $userId <= 0) {
            return null;
        }

        $sql = "
            SELECT
                note_id,
                recipe_id,
                user_id,
                note_value,
                note_date

            FROM note

            WHERE recipe_id = ?
              AND user_id = ?

            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $recipeId,
                $userId,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$row) {
                return null;
            }

            return new NoteMapping([
                'note_id' => (int) $row['note_id'],
                'recipe_id' => (int) $row['recipe_id'],
                'user_id' => (int) $row['user_id'],
                'note_value' => (int) $row['note_value'],
                'note_date' => $row['note_date'],
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Moyenne + nombre de votes
    public function getRatingStats(
        int $recipeId
    ): array {

        if ($recipeId <= 0) {
            return [
                'average_rating' => null,
                'vote_count' => 0,
            ];
        }

        $sql = "
            SELECT
                ROUND(AVG(note_value), 1) AS average_rating,
                COUNT(note_id) AS vote_count

            FROM note

            WHERE recipe_id = ?
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$recipeId]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'average_rating' =>
                    $row['average_rating'] !== null
                        ? (float) $row['average_rating']
                        : null,

                'vote_count' =>
                    (int) $row['vote_count'],
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());

            return [
                'average_rating' => null,
                'vote_count' => 0,
            ];
        }
    }
}