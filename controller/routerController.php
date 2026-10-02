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

    default:
        http_response_code(404);

        echo $twig->render('error404.html.twig', [
            'title' => 'Page introuvable',
        ]);
        break;
}