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

        return $this->twig->render('home.html.twig', [
            'title' => 'Accueil',
            'top_recipes' => $recipeManager->getTop3Recipes(),
            'recipes_menu' => $recipeManager->getRecipesMenu(),
        ]);
    }
}