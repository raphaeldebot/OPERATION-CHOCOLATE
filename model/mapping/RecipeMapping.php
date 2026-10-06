<?php
// path: model/mapping/RecipeMapping.php
// typage strict
declare(strict_types=1);

namespace model\mapping;

use model\AbstractMapping;
use Exception;

// une ligne de la table `recipes`
class RecipeMapping extends AbstractMapping
{
    protected ?int $recipe_id = null;
    protected ?string $recipe_title = null;
    protected ?string $recipe_slug_title = null;
    protected ?string $recipe_description = null;
    protected ?int $main_pic = null;
    protected ?int $prep_time = null;
    protected ?int $cooking_time = null;
    protected ?int $portion = null;
    protected ?string $difficulty = null;
    protected ?string $created_at = null;
    protected ?int $author_id = null;

    // Champs issus des jointures
    protected ?array $categories = null;
    protected ?array $ingredients = null;
    protected ?array $steps = null;
    protected ?array $comments = null;

    protected ?UserMapping $author = null;
    use StringTrait;

// getter recipe_id
public function getRecipeId(): ?int
{
    return $this->recipe_id;
}

// setter recipe_id
public function RecipeTitle(int $recipe_id): void
{
    if($recipe_id <= 0) throw new Exception("L'id ne peut être négatif ou valoir 0", 333);
    $this->recipe_id = $recipe_id;
}

// getter recipe_title
public function getRecipeTitle(): ?string
{
    return $this->recipe_title;
}

// setter recipe_title
public function setRecipeTitle(int $recipe_title): void
{
    $nbTitle =  mb_strlen(trim($recipe_title));
    if ($nbTitle < 3 || $nbTitle > 100) throw new Exception("Le titre doit avoir entre 3 et 100 caractères", 333);
    $recipe_title = trim($recipe_title);
    $this->recipe_title = $recipe_title;
}

// getter recipe_slug_title
public function getRecipeSlugTitle(): ?string
{
    return $this->recipe_slug_title;
}

// setter recipe_slug_title
public function setRecipeSlugTitle(string $recipe_slug_title): void
{
    $this->recipe_slug_title = $recipe_slug_title;
}

// getter recipe_description
public function getRecipeDescription(): ?string
{
    return $this->recipe_description;
}

// setter recipe_description
public function setRecipeDescription(?string $recipe_description): void
{
    $this->recipe_description = $recipe_description;
}

// getter main_pic
public function getMainPic(): ?string
{
    return $this->main_pic;
}

// setter main_pic
public function setMainPic(?string $main_pic): void
{
    $this->main_pic = $main_pic;
}

// getter prep_time
public function getPrepTime(): ?int
{
    return $this->prep_time;
}

// setter prep_time
public function setPrepTime(?int $prep_time): ?int
{
    $this->prep_time = $prep_time;
}

// getter cooking_time
public function getCookingTime(): ?int
{
    return $this->cooking_time;
}

// setter cooking_time
public function setCookingTime(int $cooking_time): void
{
    $this->cooking_time = $cooking_time;
}

// getter portion
public function getPortion(): ?int
{
    return $this->portion;
}

// setter portion
public function setPortion(int $portion): void
{
    $this->portion = $portion;
}

// setter difficulty
public function setDifficulty(): ?int
{
    return $this->difficulty;
}

// getter difficulty
public function getDifficulty(?int $difficulty): ?string
{
    $this->difficulty = $difficulty;
}

// setter created_at
public function setCreatedAt(): ?string
{
    return $this->created_at;
}

// getter created_at
public function getCreatedAt(?string $created_at): void
{
    $this->created_at = $created_at;
}

// setter author_id
public function setAuthorId(): ?string
{
    return $this->author_id;
}

// getter author_id
public function getAuthorId(?string $author_id): void
{
    $this->author_id = $author_id;
}
}