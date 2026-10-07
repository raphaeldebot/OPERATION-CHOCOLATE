<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use model\ManagerInterface;
use model\mapping\RecipeMapping;
use model\mapping\UserMapping;
use model\mapping\CategoryMapping;
use model\mapping\IngredientMapping;
use model\mapping\StepMapping;

class RecipeManager implements ManagerInterface
{
    private PDO $db;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Liste légère utilisée dans le menu
    public function getRecipesMenu(): array
    {
        $sql = "
            SELECT
                recipe_id,
                recipe_title,
                recipe_slug_title
            FROM recipe
            ORDER BY recipe_title ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $recipes = [];

            foreach ($rows as $row) {
                $recipes[] = new RecipeMapping($row);
            }

            return $recipes;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Liste des recettes pour la page Recettes
    public function getAllRecipes(): array
    {
        $sql = "
            SELECT
                r.*,
                rating.average_rating,
                COALESCE(rating.vote_count, 0) AS vote_count
            FROM recipe r

            LEFT JOIN (
                SELECT
                    recipe_id,
                    ROUND(AVG(note_value), 1) AS average_rating,
                    COUNT(note_id) AS vote_count
                FROM note
                GROUP BY recipe_id
            ) rating
                ON rating.recipe_id = r.recipe_id

            ORDER BY r.created_at DESC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $recipes = [];

            foreach ($rows as $row) {

                $recipe = new RecipeMapping($row);

                $recipe->setCategories(
                    $this->getCategoriesByRecipeId(
                        $recipe->getRecipeId()
                    )
                );

                $recipes[] = [
                    'recipe' => $recipe,

                    'average_rating' =>
                        $row['average_rating'] !== null
                            ? (float) $row['average_rating']
                            : null,

                    'vote_count' =>
                        (int) $row['vote_count'],

                    'total_time' =>
                        (int) $recipe->getPrepTime()
                        + (int) $recipe->getCookingTime(),
                ];
            }

            return $recipes;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Détail complet d'une recette via son slug
    public function getRecipeBySlug(
        string $slug,
        ?int $userId = null
    ): ?array {

        $sql = "
            SELECT
                r.*,
                u.user_id,
                u.user_login
            FROM recipe r

            INNER JOIN user u
                ON u.user_id = r.author_id

            WHERE r.recipe_slug_title = ?

            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$slug]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$row) {
                return null;
            }

            $recipe = new RecipeMapping($row);

            // Auteur
            $author = new UserMapping([
                'user_id' => $row['user_id'],
                'user_login' => $row['user_login'],
            ]);

            $recipe->setAuthor($author);

            $recipeId = $recipe->getRecipeId();

            // Relations
            $recipe->setCategories(
                $this->getCategoriesByRecipeId($recipeId)
            );

            $recipe->setIngredients(
                $this->getIngredientsByRecipeId($recipeId)
            );

            $recipe->setSteps(
                $this->getStepsByRecipeId($recipeId)
            );

            // Notes
            $rating = $this->getRatingStats($recipeId);

            $userRating = null;

            if ($userId !== null) {
                $userRating = $this->getUserRating(
                    $recipeId,
                    $userId
                );
            }

            return [
                'recipe' => $recipe,

                'average_rating' =>
                    $rating['average_rating'],

                'vote_count' =>
                    $rating['vote_count'],

                'user_rating' =>
                    $userRating,

                'total_time' =>
                    (int) $recipe->getPrepTime()
                    + (int) $recipe->getCookingTime(),
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Top 3 pour la page d'accueil
    public function getTop3Recipes(): array
    {
        $sql = "
            SELECT
                r.*,
                rating.average_rating,
                COALESCE(rating.vote_count, 0) AS vote_count

            FROM recipe r

            LEFT JOIN (
                SELECT
                    recipe_id,
                    ROUND(AVG(note_value), 1) AS average_rating,
                    COUNT(note_id) AS vote_count
                FROM note
                GROUP BY recipe_id
            ) rating
                ON rating.recipe_id = r.recipe_id

            ORDER BY
                (COALESCE(rating.vote_count, 0) > 0) DESC,
                rating.average_rating DESC,
                rating.vote_count DESC,
                r.created_at DESC

            LIMIT 3
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $recipes = [];

            foreach ($rows as $row) {

                $recipe = new RecipeMapping($row);

                $recipes[] = [
                    'recipe' => $recipe,

                    'average_rating' =>
                        $row['average_rating'] !== null
                            ? (float) $row['average_rating']
                            : null,

                    'vote_count' =>
                        (int) $row['vote_count'],

                    'total_time' =>
                        (int) $recipe->getPrepTime()
                        + (int) $recipe->getCookingTime(),
                ];
            }

            return $recipes;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Catégories d'une recette
    private function getCategoriesByRecipeId(
        int $recipeId
    ): array {

        $sql = "
            SELECT c.*
            FROM category c

            INNER JOIN recipe_category rc
                ON rc.category_id = c.category_id

            WHERE rc.recipe_id = ?

            ORDER BY c.category_title ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $categories = [];

        foreach ($rows as $row) {
            $categories[] = new CategoryMapping($row);
        }

        return $categories;
    }

    // Ingrédients + quantité/unité
    private function getIngredientsByRecipeId(
        int $recipeId
    ): array {

        $sql = "
            SELECT
                i.*,
                ri.recipe_ingredient_quantity,
                ri.recipe_ingredient_unity

            FROM ingredient i

            INNER JOIN recipe_ingredient ri
                ON ri.ingredient_id = i.ingredient_id

            WHERE ri.recipe_id = ?

            ORDER BY i.ingredient_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $ingredients = [];

        foreach ($rows as $row) {
            $ingredients[] = new IngredientMapping($row);
        }

        return $ingredients;
    }

    // Étapes d'une recette dans le bon ordre
    private function getStepsByRecipeId(
        int $recipeId
    ): array {

        $sql = "
            SELECT *
            FROM step
            WHERE recipe_id = ?
            ORDER BY step_order ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $steps = [];

        foreach ($rows as $row) {
            $steps[] = new StepMapping($row);
        }

        return $steps;
    }

    // Moyenne + nombre de votes
    private function getRatingStats(
        int $recipeId
    ): array {

        $sql = "
            SELECT
                ROUND(AVG(note_value), 1) AS average_rating,
                COUNT(note_id) AS vote_count
            FROM note
            WHERE recipe_id = ?
        ";

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
    }

    // Note personnelle de l'utilisateur
    private function getUserRating(
        int $recipeId,
        int $userId
    ): ?int {

        $sql = "
            SELECT note_value
            FROM note
            WHERE recipe_id = ?
              AND user_id = ?
            LIMIT 1
        ";

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

        return (int) $row['note_value'];
    }
}