<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class CommentMapping extends AbstractMapping
{
    protected ?int $id_comment = null;
    protected ?int $user_id = null;
    protected ?int $recipe_id = null;
    protected ?string $comment_subject = null;
    protected ?string $comment_message = null;
    protected ?string $comment_date = null;
    protected ?string $comment_status = null;

    // Relations
    protected ?UserMapping $author = null;
    protected ?RecipeMapping $recipe = null;

// getter id_comment
public function getIdComment(): ?int
{
    return $this->id_comment;
}

// setter id_comment
public function setIdComment(?int $id_comment): void
{
    if ($id_comment === null) {
        return;
    }
    if ($id_comment <= 0) {
        throw new Exception("id_comment doit être un entier positif");
    }
    $this->id_comment = $id_comment;
}

// getter user_id
public function getUserId(): ?int
{
    return $this->user_id;
}

// setter user_id
public function setUserId(int $user_id): void
{
    if ($user_id <= 0) {
        throw new Exception("user_id doit être un entier positif");
    }
    $this->user_id = $user_id;
}

// getter recipe_id
public function getRecipeId(): ?int
{
    return $this->recipe_id;
}

// setter recipe_id
public function setRecipeId(int $recipe_id): void
{
    if ($recipe_id <= 0) {
        throw new Exception("recipe_id doit être un entier positif");
    }
    $this->recipe_id = $recipe_id;
}

// getter comment_subject
public function getCommentSubject(): ?string
{
    return $this->comment_subject;
}

// setter comment_subject
public function setCommentSubject(?string $comment_subject): void
{
    if ($comment_subject === null) {
        $this->comment_subject = null;
        return;
    }
    $comment_subject = trim($comment_subject);
    if ($comment_subject === '') {
        $this->comment_subject = null;
        return;
    }
    if (mb_strlen($comment_subject) > 120) {
        throw new Exception(
            "Le sujet du commentaire ne peut pas dépasser 120 caractères"
        );
    }
    $this->comment_subject = $comment_subject;
}

// getter comment_message
public function getCommentMessage(): ?string
{
    return $this->comment_message;
}

// setter comment_message
public function setCommentMessage(string $comment_message): void
{
    $comment_message = trim($comment_message);
    $length = mb_strlen($comment_message);
    if ($length < 3 || $length > 500) {
        throw new Exception(
            "Le commentaire doit avoir entre 3 et 500 caractères"
        );
    }
    $this->comment_message = $comment_message;
}

// getter comment_date
public function getCommentDate(): ?string
{
    return $this->comment_date;
}

// setter comment_date
public function setCommentDate(?string $comment_date): void
{
    if ($comment_date === null) {
        return;
    }
    $this->comment_date = $comment_date;
}

// getter comment_status
public function getCommentStatus(): ?string
{
    return $this->comment_status;
}

// getter comment_status
public function setCommentStatus(string $comment_status): void
{
    if (!in_array($comment_status, ['published', 'hidden'], true)) {
        throw new Exception("Statut du commentaire invalide");
    }
    $this->comment_status = $comment_status;
}

// LES MISE EN RELATION 
// relation avec l'auteur
// getter author
public function getAuthor(): ?UserMapping
{
    return $this->author;
}

// setter author
public function setAuthor(?UserMapping $author): void
{
    $this->author = $author;
}

// relation avec la recette 
// getter recipe
public function getRecipe(): ?RecipeMapping
{
    return $this->recipe;
}

// setter recipe
public function setRecipe(?RecipeMapping $recipe): void
{
    $this->recipe = $recipe;
}
}