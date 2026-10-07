<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class StepMapping extends AbstractMapping
{
    protected ?int $step_id = null;
    protected ?int $recipe_id = null;
    protected ?int $step_order = null;
    protected ?string $step_title = null;
    protected ?string $step_texte = null;
    protected ?string $step_pic = null;

// getter step_id
public function getStepId(): ?int
{
    return $this->step_id;
}

// setter step_id
public function setStepId(?int $step_id): void
{
    if ($step_id === null) {
        return;
    }
    if ($step_id <= 0) {
        throw new Exception("step_id doit être un entier positif");
    }
    $this->step_id = $step_id;
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

// getter step_order
public function getStepOrder(): ?int
{
    return $this->step_order;
}

// setter step_order
public function setStepOrder(int $step_order): void
{
    if ($step_order <= 0) {
        throw new Exception("L'ordre de l'étape doit être supérieur à 0");
    }
    $this->step_order = $step_order;
}

// getter step_title 
public function getStepTitle(): ?string
{
    return $this->step_title;
}

// setter step_title
public function setStepTitle(string $step_title): void
{
    $step_title = trim($step_title);
    if (mb_strlen($step_title) < 2 || mb_strlen($step_title) > 100) {
        throw new Exception(
            "Le titre de l'étape doit avoir entre 2 et 100 caractères"
        );
    }
    $this->step_title = $step_title;
}

// getter step_texte
public function getStepTexte(): ?string
{
    return $this->step_texte;
}

// setter step_texte
public function setStepTexte(string $step_texte): void
{
    $step_texte = trim($step_texte);
    if (mb_strlen($step_texte) < 3) {
        throw new Exception(
            "Le texte de l'étape doit contenir au moins 3 caractères"
        );
    }
    $this->step_texte = $step_texte;
}

// getter step_pic
public function getStepPic(): ?string
{
    return $this->step_pic;
}

// setter step_pic
public function setStepPic(?string $step_pic): void
{
    if ($step_pic === null) {
        $this->step_pic = null;
        return;
    }
    $step_pic = trim($step_pic);
    if (mb_strlen($step_pic) > 255) {
        throw new Exception(
            "Le chemin de l'image ne peut pas dépasser 255 caractères"
        );
    }
    $this->step_pic = $step_pic;
}
}