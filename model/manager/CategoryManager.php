<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use model\ManagerInterface;
use model\mapping\CategoryMapping;

class CategoryManager implements ManagerInterface
{
    private PDO $db;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Toutes les catégories
    public function getAllCategories(): array
    {
        $sql = "
            SELECT
                category_id,
                category_title,
                category_slug_title,
                category_description
            FROM category
            ORDER BY category_title ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $categories = [];

            foreach ($rows as $row) {
                $categories[] = new CategoryMapping($row);
            }

            return $categories;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Catégorie via son slug
    public function getCategoryBySlug(
        string $slug
    ): ?CategoryMapping {

        $sql = "
            SELECT
                category_id,
                category_title,
                category_slug_title,
                category_description
            FROM category
            WHERE category_slug_title = ?
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([trim($slug)]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$row) {
                return null;
            }

            return new CategoryMapping($row);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Catégorie via son ID
    public function getCategoryById(
        int $id
    ): ?CategoryMapping {

        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                category_id,
                category_title,
                category_slug_title,
                category_description
            FROM category
            WHERE category_id = ?
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

            return new CategoryMapping($row);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}