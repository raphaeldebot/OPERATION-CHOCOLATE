<?php

declare(strict_types=1);

use controller\HomeController;
use controller\RecipeController;
use controller\UserController;
use controller\CommentController;
use controller\NoteController;
use controller\ContactController;

// Route demandée
$page = is_string($_GET['pg'] ?? null)
? $_GET['pg']
: 'home';

// Lire les données JSON ou POST
$readInput = static function (): array {
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';



if (str_contains($contentType, 'application/json')) {
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );
    return is_array($data) ? $data : [];
}
return $_POST;
};

// Vérifier la méthode HTTP
$requireMethod = static function (string $expected): bool {
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === $expected) {
return true;
}



http_response_code(405);
header('Allow: ' . $expected);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'message' => 'Méthode non autorisée',
    'data' => null,
]);
return false;
};

switch ($page) {



// Pages
case 'home':
    echo (new HomeController($connectDB, $twig))->index();
    break;
case 'recipes':
    echo (new RecipeController($connectDB, $twig))->index();
    break;
case 'recipe':
    $slug = is_string($_GET['slug'] ?? null)
        ? $_GET['slug']
        : '';
    echo (new RecipeController($connectDB, $twig))
        ->show($slug);
    break;
case 'about':
    echo $twig->render('about.html.twig', [
        'title' => 'À propos',
    ]);
    break;
case 'contact':
    echo (new ContactController($connectDB, $twig))->index();
    break;
// Utilisateurs
case 'api-register':
    if (!$requireMethod('POST')) break;
    echo (new UserController($connectDB))
        ->register($readInput());
    break;
case 'api-login':
    if (!$requireMethod('POST')) break;
    echo (new UserController($connectDB))
        ->login($readInput());
    break;
case 'api-logout':
    if (!$requireMethod('POST')) break;
    echo (new UserController($connectDB))
        ->logout($readInput());
    break;
case 'api-me':
    if (!$requireMethod('GET')) break;
    echo (new UserController($connectDB))->me();
    break;
// Commentaires
case 'api-comments':
    if (!$requireMethod('GET')) break;
    $recipeId = filter_var(
        $_GET['recipe_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $offset = filter_var(
        $_GET['offset'] ?? 0,
        FILTER_VALIDATE_INT
    );
    echo (new CommentController($connectDB))->index(
        $recipeId === false || $recipeId === null ? 0 : $recipeId,
        $offset === false ? -1 : $offset
    );
    break;
case 'api-comment-add':
    if (!$requireMethod('POST')) break;
    echo (new CommentController($connectDB))
        ->store($readInput());
    break;
case 'api-comment-delete':
    if (!$requireMethod('POST')) break;
    echo (new CommentController($connectDB))
        ->delete($readInput());
    break;
// Notes
case 'api-note-me':
    if (!$requireMethod('GET')) break;

    $recipeId = filter_var(
        $_GET['recipe_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    echo (new NoteController($connectDB))->mine(
        is_int($recipeId) ? $recipeId : 0
    );
    break;
case 'api-note-rate':
    if (!$requireMethod('POST')) break;
    echo (new NoteController($connectDB))
        ->rate($readInput());
    break;
// Contact
case 'api-contact':
    if (!$requireMethod('POST')) break;
    echo (new ContactController($connectDB, $twig))
        ->submit($readInput());
    break;
// Page inconnue
default:
    http_response_code(404);
    if (str_starts_with($page, 'api-')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Route introuvable',
            'data' => null,
        ]);
    } else {
        echo $twig->render('error404.html.twig', [
            'title' => 'Page introuvable',
        ]);
    }
    break;
}