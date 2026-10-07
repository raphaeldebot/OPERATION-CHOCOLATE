<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use model\ManagerInterface;
use model\mapping\StepMapping;

class StepManager implements ManagerInterface
{
    private PDO $db;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Toutes les étapes d'une recette
    public function getStepsByRecipeId(
        int $recipeId
    ): array {

        if ($recipeId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                step_id,
                recipe_id,
                step_order,
                step_title,
                step_texte,
                step_pic
            FROM step
            WHERE recipe_id = ?
            ORDER BY step_order ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$recipeId]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $steps = [];

            foreach ($rows as $row) {
                $steps[] = new StepMapping($row);
            }

            return $steps;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Une étape via son ID
    public function getStepById(
        int $id
    ): ?StepMapping {

        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                step_id,
                recipe_id,
                step_order,
                step_title,
                step_texte,
                step_pic
            FROM step
            WHERE step_id = ?
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

            return new StepMapping($row);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}