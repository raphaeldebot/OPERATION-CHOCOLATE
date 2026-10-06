<?php
// path: model/mapping/RecipeMapping.php
// typage strict
declare(strict_types=1);

namespace model\mapping;

use model\StringTrait;
use model\AbstractMapping;
use Exception;

// une ligne de la table `recipes`
class RecipeMapping extends AbstractMapping
{
    protected ?int $recipe_id = null;
    protected ?string $recipe_title = null;
    protected ?string $recipe_slug_title = null;
    protected ?string $recipe_description = null;
    protected ?string $main_pic = null;
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
public function setRecipeId(int $recipe_id): void
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
public function setRecipeTitle(string $recipe_title): void
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
public function setPrepTime(?int $prep_time): void
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

// getter difficulty
public function getDifficulty(): ?string
{
    return $this->difficulty;
}

// setter difficulty
public function setDifficulty(string $difficulty): void
{
    if (!in_array($difficulty, ['facile', 'moyen', 'difficile'], true)) {
        throw new Exception('Difficulté invalide');
    }

    $this->difficulty = $difficulty;
}

// getter created_at
public function getCreatedAt(): ?string
{
    return $this->created_at;
}

// setter created_at
public function setCreatedAt(string $created_at): void
{
    $this->created_at = $created_at;
}

// getter author_id
public function getAuthorId(): ?int
{
    return $this->author_id;
}
// setter author_id
public function setAuthorId(int $author_id): void
{
    $this->author_id = $author_id;
}
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

public function getCategories(): ?array
{
    return $this->categories;
}

public function setCategories(?array $categories): void
{
    $this->categories = $categories;
}

public function getIngredients(): ?array
{
    return $this->ingredients;
}

public function setIngredients(?array $ingredients): void
{
    $this->ingredients = $ingredients;
}

public function getSteps(): ?array
{
    return $this->steps;
}

public function setSteps(?array $steps): void
{
    $this->steps = $steps;
}

public function getComments(): ?array
{
    return $this->comments;
}

public function setComments(?array $comments): void
{
    $this->comments = $comments;
}
}