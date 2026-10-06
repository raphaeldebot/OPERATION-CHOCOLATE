<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class CategoryMapping extends AbstractMapping
{
    protected ?int $categories_id = null;
    protected ?string $categories_title = null;
    protected ?string $categories_slug_title = null;
    protected ?string $categories_description = null;


// getter category_id
public function getCategoryId(): ?int
{
    return $this->category_id;
}

// setter category_id
public function setCategoryId(?int $category_id): void
{
    if ($category_id === null) {
        return;
    }

    if ($category_id <= 0) {
        throw new Exception("category_id doit être un entier positif");
    }

    $this->category_id = $category_id;
}

// getter category_title
public function getCategoryTitle(): ?string
{
    return $this->category_title;
}

// setter category_title
public function setCategoryTitle(string $category_title): void
{
    $category_title = trim($category_title);

    if (mb_strlen($category_title) < 2 || mb_strlen($category_title) > 45) {
        throw new Exception("Le titre de la catégorie doit avoir entre 2 et 45 caractères");
    }

    $this->category_title = $category_title;
}

// getter category_slug_title
public function getCategorySlugTitle(): ?string
{
    return $this->category_slug_title;
}

// setter category_slug_title
public function setCategorySlugTitle(string $category_slug_title): void
{
    $category_slug_title = trim($category_slug_title);

    if (mb_strlen($category_slug_title) < 2 || mb_strlen($category_slug_title) > 100) {
        throw new Exception("Le slug de la catégorie doit avoir entre 2 et 100 caractères");
    }

    $this->category_slug_title = $category_slug_title;
}

// getter category_description
    public function getCategoryDescription(): ?string
    {
        return $this->category_description;
    }
 
    // setter category_description
    public function setCategoryDescription(?string $category_description): void
    {
        // colonne NOT NULL : une description vide ('') est acceptée (comme dans le jeu d'essai)
        $category_description = htmlspecialchars(strip_tags(trim((string) $category_description)));
 
        // VARCHAR(255) dans la base
        if (mb_strlen($category_description) > 255) {
            throw new Exception("La description de la catégorie ne peut pas dépasser 255 caractères");
        }
 
        $this->category_description = $category_description;
    }
}

 