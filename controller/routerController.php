<?php

declare(strict_types=1);

// Page demandée
$page = $_GET['pg'] ?? 'home';

switch ($page) {

    case 'home':
        echo $twig->render('home.html.twig', [
            'title' => 'Accueil',
        ]);
        break;

    case 'contact':
        echo $twig->render('contact.html.twig', [
            'title' => 'Contact',
        ]);
        break;

    case 'about':
        echo $twig->render('about.html.twig', [
            'title' => 'Á propos',
        ]);
        break;

    case 'recipe':
        echo $twig->render('recipe.html.twig', [
            'title' => 'Recettes',
        ]);
        break;

    case 'recipes':
        echo $twig->render('recipes.html.twig', [
            'title' => 'Détail recette',
        ]);
        break;

    default:
        http_response_code(404);

        echo $twig->render('error404.html.twig', [
            'title' => 'Page introuvable',
        ]);
        break;
}