<?php

declare(strict_types=1);

namespace controller;

use Exception;
use PDO;
use model\manager\CommentManager;
use model\mapping\CommentMapping;

class CommentController
{
private CommentManager $commentManager;



public function __construct(PDO $connect)
{
    $this->commentManager = new CommentManager($connect);
}
// Récupérer les commentaires d'une recette
public function index(int $recipeId, int $offset = 0): string
{
    if ($recipeId <= 0 || $offset < 0) {
        return $this->json(false, 'Paramètres invalides', null, 400);
    }
    $comments = $this->commentManager->getCommentsByRecipeId(
        $recipeId,
        $offset
    );
    return $this->json(true, 'Commentaires récupérés', [
        'comments' => array_map(
            fn (CommentMapping $comment) => $this->formatComment($comment),
            $comments
        ),
        'next_offset' => $offset + count($comments),
    ]);
}
// Ajouter un commentaire
public function store(array $data): string
{
    $userId = $_SESSION['user']['user_id'] ?? null;
    if (!$userId) {
        return $this->json(false, 'Connexion requise', null, 401);
    }
    if (!$this->validCsrf($data)) {
        return $this->json(false, 'Requête refusée', null, 403);
    }
    $recipeId = filter_var(
        $data['recipe_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if (
        $recipeId === false ||
        $recipeId === null ||
        !is_string($data['comment_message'] ?? null) ||
        !in_array(
            gettype($data['comment_subject'] ?? null),
            ['string', 'NULL'],
            true
        )
    ) {
        return $this->json(false, 'Données invalides', null, 400);
    }
    try {
        $comment = $this->commentManager->addComment(
            (int) $userId,
            (int) $recipeId,
            $data['comment_subject'] ?? null,
            $data['comment_message']
        );
        if ($comment === null) {
            return $this->json(
                false,
                'Commentaire non enregistré (limite atteinte ou erreur)',
                null,
                400
            );
        }
        return $this->json(
            true,
            'Commentaire ajouté',
            ['comment' => $this->formatComment($comment)],
            201
        );
    } catch (Exception $e) {
        return $this->json(false, $e->getMessage(), null, 400);
    }
}
// Supprimer un commentaire
public function delete(array $data): string
{
    $userId = $_SESSION['user']['user_id'] ?? null;
    $userRole = $_SESSION['user']['user_role'] ?? 'user';
    if (!$userId) {
        return $this->json(false, 'Connexion requise', null, 401);
    }
    if (!$this->validCsrf($data)) {
        return $this->json(false, 'Requête refusée', null, 403);
    }
    $commentId = filter_var(
        $data['id_comment'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if (!$commentId) {
        return $this->json(false, 'ID invalide', null, 400);
    }
    $comment = $this->commentManager->getCommentById($commentId);
    if ($comment === null) {
        return $this->json(false, 'Commentaire introuvable', null, 404);
    }
    if (
        $comment->getUserId() !== (int) $userId &&
        $userRole !== 'admin'
    ) {
        return $this->json(false, 'Accès interdit', null, 403);
    }
    $deleted = $this->commentManager->deleteComment(
        $commentId,
        (int) $userId,
        $userRole
    );
    if (!$deleted) {
        return $this->json(false, 'Suppression impossible', null, 500);
    }
    return $this->json(true, 'Commentaire supprimé');
}
// Préparer les données envoyées au frontend
private function formatComment(CommentMapping $comment): array
{
    return [
        'id_comment' => $comment->getIdComment(),
        'recipe_id' => $comment->getRecipeId(),
        'user_id' => $comment->getUserId(),
        'user_login' => $comment->getUser()?->getUserLogin(),
        'comment_subject' => $comment->getCommentSubject(),
        'comment_message' => $comment->getCommentMessage(),
        'comment_date' => $comment->getCommentDate(),
    ];
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
    ], JSON_THROW_ON_ERROR);
}
}