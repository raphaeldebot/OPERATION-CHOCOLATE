<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Démarrage de la session
session_start();

// Racine du projet
$rootPath = dirname(__DIR__);

// Autoload Composer
require_once $rootPath . '/vendor/autoload.php';
require_once $rootPath . '/config/Database.php';

// Configuration de Twig
$loader = new FilesystemLoader($rootPath . '/view');

$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

// Chargement du routeur
require_once $rootPath . '/controller/routerController.php';