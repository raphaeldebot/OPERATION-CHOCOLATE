<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class IngredientMapping extends AbstractMapping
{
    protected ?int $ingredient_id = null;
    protected ?string $ingredient_name = null;

    // Champs issus de la table de liaison recipe_ingredient
    protected ?string $recipe_ingredient_quantity = null;
    protected ?string $recipe_ingredient_unity = null;


    // getter ingredient_id
    public function getIngredientId(): ?int
    {
        return $this->ingredient_id;
    }

    // setter ingredient_id
    public function setIngredientId(?int $ingredient_id): void
    {
        if ($ingredient_id === null) {
            return;
        }

        if ($ingredient_id <= 0) {
            throw new Exception(
                "ingredient_id doit être un entier positif"
            );
        }

        $this->ingredient_id = $ingredient_id;
    }


    // getter ingredient_name
    public function getIngredientName(): ?string
    {
        return $this->ingredient_name;
    }

    // setter ingredient_name
    public function setIngredientName(string $ingredient_name): void
    {
        $ingredient_name = trim($ingredient_name);

        if ($ingredient_name === '') {
            throw new Exception(
                "Le nom de l'ingrédient est obligatoire"
            );
        }

        if (mb_strlen($ingredient_name) > 100) {
            throw new Exception(
                "Le nom de l'ingrédient ne peut pas dépasser 100 caractères"
            );
        }

        $this->ingredient_name = $ingredient_name;
    }


    // getter recipe_ingredient_quantity
    public function getRecipeIngredientQuantity(): ?string
    {
        return $this->recipe_ingredient_quantity;
    }

    // setter recipe_ingredient_quantity
    public function setRecipeIngredientQuantity(
        ?string $recipe_ingredient_quantity
    ): void {
        if ($recipe_ingredient_quantity === null) {
            $this->recipe_ingredient_quantity = null;
            return;
        }

        if (!is_numeric($recipe_ingredient_quantity)) {
            throw new Exception(
                "La quantité de l'ingrédient doit être numérique"
            );
        }

        $this->recipe_ingredient_quantity = $recipe_ingredient_quantity;
    }


    // getter recipe_ingredient_unity
    public function getRecipeIngredientUnity(): ?string
    {
        return $this->recipe_ingredient_unity;
    }

    // setter recipe_ingredient_unity
    public function setRecipeIngredientUnity(
        ?string $recipe_ingredient_unity
    ): void {
        if ($recipe_ingredient_unity === null) {
            $this->recipe_ingredient_unity = null;
            return;
        }

        $recipe_ingredient_unity = trim($recipe_ingredient_unity);

        if (mb_strlen($recipe_ingredient_unity) > 45) {
            throw new Exception(
                "L'unité ne peut pas dépasser 45 caractères"
            );
        }

        $this->recipe_ingredient_unity = $recipe_ingredient_unity;
    }
}