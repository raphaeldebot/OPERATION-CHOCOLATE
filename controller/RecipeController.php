<?php

declare(strict_types=1);

namespace controller;

use PDO;
use Twig\Environment;
use model\manager\RecipeManager;
use model\manager\CommentManager;

class RecipeController
{
public function __construct(
private PDO $db,
private Environment $twig
) {
}



// Liste de toutes les recettes
public function index(): string
{
    $recipeManager = new RecipeManager($this->db);
    return $this->twig->render('recipes.html.twig', [
        'title' => 'Recettes',
        'recipes' => $recipeManager->getAllRecipes(),
        'recipes_menu' => $recipeManager->getRecipesMenu(),
    ]);
}
// Détail d'une recette via son slug
public function show(string $slug): string
{
    $recipeManager = new RecipeManager($this->db);
    // Récupérer l'utilisateur connecté
    $userId = isset($_SESSION['user']['user_id'])
        ? (int) $_SESSION['user']['user_id']
        : null;
    // Récupérer la recette
    $data = $recipeManager->getRecipeBySlug(
        trim($slug),
        $userId
    );
    // Recette introuvable
    if ($data === null) {
        http_response_code(404);
        return $this->twig->render('error404.html.twig', [
            'title' => 'Recette introuvable',
        ]);
    }
    // Récupérer les commentaires
    $commentManager = new CommentManager($this->db);
    $comments = $commentManager->getCommentsByRecipeId(
        $data['recipe']->getRecipeId()
    );
    return $this->twig->render('recipe.html.twig', [
        'title' => $data['recipe']->getRecipeTitle(),
        'recipe' => $data['recipe'],
        'average_rating' => $data['average_rating'],
        'vote_count' => $data['vote_count'],
        'user_rating' => $data['user_rating'],
        'total_time' => $data['total_time'],
        'comments' => $comments,
        'recipes_menu' => $recipeManager->getRecipesMenu(),
    ]);
}
}