<?php

declare(strict_types=1);

namespace controller;

use PDO;
use Twig\Environment;
use model\manager\RecipeManager;

class HomeController
{
    public function __construct(
        private PDO $db,
        private Environment $twig
    ) {
    }

    public function index(): string
{
    $recipeManager = new RecipeManager($this->db);

    $topRecipes = [];

    foreach ($recipeManager->getTop3Recipes() as $item) {
        $recipe = $item['recipe'];

        $topRecipes[] = [
            'recipe_slug_title' => $recipe->getRecipeSlugTitle(),
            'recipe_title' => $recipe->getRecipeTitle(),
            'main_pic' => $recipe->getMainPic(),
            'difficulty' => $recipe->getDifficulty(),
            'average_rating' => $item['average_rating'],
            'vote_count' => $item['vote_count'],
            'total_time' => $item['total_time'],
        ];
    }

    return $this->twig->render('home.html.twig', [
        'title' => 'Accueil',
        'top_recipes' => $topRecipes,
        'recipes_menu' => $recipeManager->getRecipesMenu(),
    ]);
}
}