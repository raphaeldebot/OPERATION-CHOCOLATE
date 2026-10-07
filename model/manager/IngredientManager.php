<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use model\ManagerInterface;
use model\mapping\IngredientMapping;

class IngredientManager implements ManagerInterface
{
    private PDO $db;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Tous les ingrédients
    public function getAllIngredients(): array
    {
        $sql = "
            SELECT
                ingredient_id,
                ingredient_name
            FROM ingredient
            ORDER BY ingredient_name ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $ingredients = [];

            foreach ($rows as $row) {
                $ingredients[] = new IngredientMapping($row);
            }

            return $ingredients;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Ingrédient via son ID
    public function getIngredientById(
        int $id
    ): ?IngredientMapping {

        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                ingredient_id,
                ingredient_name
            FROM ingredient
            WHERE ingredient_id = ?
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

            return new IngredientMapping($row);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Ingrédients d'une recette avec quantité et unité
    public function getIngredientsByRecipeId(
        int $recipeId
    ): array {

        if ($recipeId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                i.ingredient_id,
                i.ingredient_name,
                ri.recipe_ingredient_quantity,
                ri.recipe_ingredient_unity

            FROM ingredient i

            INNER JOIN recipe_ingredient ri
                ON ri.ingredient_id = i.ingredient_id

            WHERE ri.recipe_id = ?

            ORDER BY i.ingredient_name ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$recipeId]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $ingredients = [];

            foreach ($rows as $row) {
                $ingredients[] = new IngredientMapping($row);
            }

            return $ingredients;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}