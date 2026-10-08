<?php

declare(strict_types=1);

namespace controller;

use Exception;
use PDO;
use Twig\Environment;
use model\manager\ContactMessageManager;

class ContactController
{
private ContactMessageManager $contactManager;



public function __construct(
    PDO $connect,
    private Environment $twig
) {
    $this->contactManager = new ContactMessageManager($connect);
}
// Afficher le formulaire
public function index(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $this->twig->render('contact.html.twig', [
        'title' => 'Contact',
        'csrf_token' => $_SESSION['csrf_token'],
    ]);
}
// Envoyer un message
public function submit(array $data): string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return $this->json(false, 'Méthode non autorisée', 405);
    }
    // Protection CSRF
    if (
        !isset($_SESSION['csrf_token']) ||
        !is_string($data['_csrf'] ?? null) ||
        !hash_equals($_SESSION['csrf_token'], $data['_csrf'])
    ) {
        return $this->json(false, 'Requête refusée', 403);
    }
    // Vérifier les champs obligatoires
    $fields = [
        'contact_message_name',
        'contact_message_email',
        'contact_message_sujet',
        'contact_message_message',
    ];
    foreach ($fields as $field) {
        if (
            !is_string($data[$field] ?? null) ||
            trim($data[$field]) === ''
        ) {
            return $this->json(
                false,
                'Veuillez remplir correctement tous les champs',
                422
            );
        }
    }
    try {
        // Validation et enregistrement via le Manager
        $message = $this->contactManager->addMessage($data);
        if ($message === null) {
            return $this->json(
                false,
                'Envoi impossible pour le moment. Réessayez plus tard.',
                503
            );
        }
        return $this->json(
            true,
            'Votre message a bien été envoyé. Merci !',
            201
        );
    } catch (Exception $e) {
        return $this->json(
            false,
            'Veuillez vérifier les informations saisies',
            422
        );
    }
}
// Réponse JSON
private function json(
    bool $success,
    string $message,
    int $status = 200
): string {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => null,
    ], JSON_THROW_ON_ERROR);
}
}