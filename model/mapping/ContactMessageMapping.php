<?php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\AbstractMapping;

class ContactMessageMapping extends AbstractMapping
{
    protected ?int $id_contact_message = null;
    protected ?string $contact_message_name = null;
    protected ?string $contact_message_email = null;
    protected ?string $contact_message_sujet = null;
    protected ?string $contact_message_message = null;
    protected ?string $contact_message_date = null;

// getter id_contact_message
public function getIdContactMessage(): ?int
{
    return $this->id_contact_message;
}

// setter id_contact_message
public function setIdContactMessage(?int $id_contact_message): void
{
    if ($id_contact_message === null) {
        return;
    }
    if ($id_contact_message <= 0) {
        throw new Exception(
            "id_contact_message doit être un entier positif"
        );
    }
    $this->id_contact_message = $id_contact_message;
}

// getter contact_message_name
public function getContactMessageName(): ?string
{
    return $this->contact_message_name;
}

// setter contact_message_name
public function setContactMessageName(string $contact_message_name): void
{
    $contact_message_name = trim($contact_message_name);
    if ($contact_message_name === '') {
        throw new Exception("Le nom est obligatoire");
    }
    if (mb_strlen($contact_message_name) > 100) {
        throw new Exception(
            "Le nom ne peut pas dépasser 100 caractères"
        );
    }
    $this->contact_message_name = $contact_message_name;
}

// getter contact_message_email
public function getContactMessageEmail(): ?string
{
    return $this->contact_message_email;
}

// setter contact_message_email
public function setContactMessageEmail(string $contact_message_email): void
{
    $contact_message_email = trim($contact_message_email);
    if (!filter_var($contact_message_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(
            "L'adresse email n'est pas valide"
        );
    }
    if (mb_strlen($contact_message_email) > 255) {
        throw new Exception(
            "L'adresse email ne peut pas dépasser 255 caractères"
        );
    }
    $this->contact_message_email = $contact_message_email;
}

// getter contact_message_sujet
public function getContactMessageSujet(): ?string
{
    return $this->contact_message_sujet;
}

// setter contact_message_sujet
public function setContactMessageSujet(string $contact_message_sujet): void
{
    $contact_message_sujet = trim($contact_message_sujet);
    if ($contact_message_sujet === '') {
        throw new Exception("Le sujet est obligatoire");
    }
    if (mb_strlen($contact_message_sujet) > 120) {
        throw new Exception(
            "Le sujet ne peut pas dépasser 120 caractères"
        );
    }
    $this->contact_message_sujet = $contact_message_sujet;
}

// getter contact_message_message
public function getContactMessageMessage(): ?string
{
    return $this->contact_message_message;
}

// setter contact_message_message
public function setContactMessageMessage(string $contact_message_message): void
{
    $contact_message_message = trim($contact_message_message);
    if ($contact_message_message === '') {
        throw new Exception("Le message est obligatoire");
    }
    $this->contact_message_message = $contact_message_message;
}

// getter contact_message_date
public function getContactMessageDate(): ?string
{
    return $this->contact_message_date;
}

// setter contact_message_date
public function setContactMessageDate(?string $contact_message_date): void
{
    if ($contact_message_date === null) {
        return;
    }
    $this->contact_message_date = $contact_message_date;
}
}