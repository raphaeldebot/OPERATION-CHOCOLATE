<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class NoteMapping extends AbstractMapping
{
    protected ?int $note_id = null;
    protected ?int $recipe_id = null;
    protected ?int $user_id = null;
    protected ?int $note_value = null;
    protected ?string $note_date = null;

    // Relations
    protected ?UserMapping $user = null;
    protected ?RecipeMapping $recipe = null;

// getter note_id
public function getNoteId(): ?int
{
    return $this->note_id;
}

// setter note_id
public function setNoteId(?int $note_id): void
{
    if ($note_id === null) {
        return;
    }
    if ($note_id <= 0) {
        throw new Exception("note_id doit être un entier positif");
    }
    $this->note_id = $note_id;
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

// getter note_value
public function getNoteValue(): ?int
{
    return $this->note_value;
}

// setter note_value
public function setNoteValue(int $note_value): void
{
    if ($note_value < 1 || $note_value > 5) {
        throw new Exception("La note doit être comprise entre 1 et 5");
    }
    $this->note_value = $note_value;
}

// getter note_date
public function getNoteDate(): ?string
{
    return $this->note_date;
}

// setter note_date
public function setNoteDate(?string $note_date): void
{
    if ($note_date === null) {
        return;
    }
    $this->note_date = $note_date;
}

// LES MISE EN RELATION 
// relation avec le user
// getter user
public function getUser(): ?UserMapping
{
    return $this->user;
}

// setter user
public function setUser(?UserMapping $user): void
{
    $this->user = $user;
}

// relation avec la recipe
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