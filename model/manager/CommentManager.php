<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use model\ManagerInterface;
use model\mapping\CommentMapping;
use model\mapping\UserMapping;

class CommentManager implements ManagerInterface
{
    private PDO $db;

    private const COMMENTS_PER_PAGE = 10;
    private const MAX_COMMENTS = 5;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Commentaires publiés d'une recette
    public function getCommentsByRecipeId(
        int $recipeId,
        int $offset = 0
    ): array {

        if ($recipeId <= 0 || $offset < 0) {
            return [];
        }

        $sql = "
            SELECT
                c.id_comment,
                c.user_id,
                c.recipe_id,
                c.comment_subject,
                c.comment_message,
                c.comment_date,
                c.comment_status,
                u.user_login

            FROM comment c

            INNER JOIN user u
                ON u.user_id = c.user_id

            WHERE c.recipe_id = ?
              AND c.comment_status = 'published'

            ORDER BY c.comment_date DESC

            LIMIT 10 OFFSET ?
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(
                1,
                $recipeId,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                2,
                $offset,
                PDO::PARAM_INT
            );

            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $comments = [];

            foreach ($rows as $row) {

                $comment = new CommentMapping($row);

                $author = new UserMapping([
                    'user_id' => $row['user_id'],
                    'user_login' => $row['user_login'],
                ]);

                $comment->setAuthor($author);

                $comments[] = $comment;
            }

            return $comments;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Commentaire via son ID
    public function getCommentById(
        int $id
    ): ?CommentMapping {

        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                c.id_comment,
                c.user_id,
                c.recipe_id,
                c.comment_subject,
                c.comment_message,
                c.comment_date,
                c.comment_status,
                u.user_login

            FROM comment c

            INNER JOIN user u
                ON u.user_id = c.user_id

            WHERE c.id_comment = ?

            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$row) {
                return null;
            }

            $comment = new CommentMapping($row);

            $comment->setAuthor(
                new UserMapping([
                    'user_id' => $row['user_id'],
                    'user_login' => $row['user_login'],
                ])
            );

            return $comment;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Ajouter un commentaire
    public function addComment(
        int $userId,
        int $recipeId,
        ?string $subject,
        string $message
    ): ?CommentMapping {

        if ($userId <= 0 || $recipeId <= 0) {
            return null;
        }

        if ($this->hasReachedCommentLimit($userId)) {
            return null;
        }

        // Validation via CommentMapping
        $comment = new CommentMapping([
            'user_id' => $userId,
            'recipe_id' => $recipeId,
            'comment_subject' => $subject,
            'comment_message' => $message,
            'comment_status' => 'published',
        ]);

        $sql = "
            INSERT INTO comment (
                user_id,
                recipe_id,
                comment_subject,
                comment_message,
                comment_status
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $comment->getUserId(),
                $comment->getRecipeId(),
                $comment->getCommentSubject(),
                $comment->getCommentMessage(),
                $comment->getCommentStatus(),
            ]);

            return $this->getCommentById(
                (int) $this->db->lastInsertId()
            );

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Supprimer un commentaire
    public function deleteComment(
        int $commentId,
        int $userId,
        string $userRole
    ): bool {

        $comment = $this->getCommentById($commentId);

        if ($comment === null) {
            return false;
        }

        // Seul l'auteur ou un administrateur peut supprimer
        if (
            $comment->getUserId() !== $userId
            && $userRole !== 'admin'
        ) {
            return false;
        }

        $sql = "
            DELETE FROM comment
            WHERE id_comment = ?
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $commentId
            ]);

            return $stmt->rowCount() === 1;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Limite anti-spam :
    // maximum 5 commentaires en 10 minutes
    private function hasReachedCommentLimit(
        int $userId
    ): bool {

        $sql = "
            SELECT COUNT(*) AS comment_count

            FROM comment

            WHERE user_id = ?
              AND comment_date >=
                  DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return (int) $row['comment_count']
                >= self::MAX_COMMENTS;

        } catch (Exception $e) {
            error_log($e->getMessage());

            // En cas de problème DB,
            // on refuse plutôt que contourner la limite.
            return true;
        }
    }
}