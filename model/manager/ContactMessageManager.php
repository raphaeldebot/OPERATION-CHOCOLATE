<?php

declare(strict_types=1);

namespace model\manager;

use Exception;
use PDO;
use model\ManagerInterface;
use model\mapping\ContactMessageMapping;

class ContactMessageManager implements ManagerInterface
{
    private PDO $db;

    private const MAX_MESSAGES = 3;

    public function __construct(PDO $connect)
    {
        $this->db = $connect;
    }

    // Ajouter un message de contact
    public function addMessage(
        array $data
    ): ?ContactMessageMapping {

        if (
            !isset(
                $data['contact_message_name'],
                $data['contact_message_email'],
                $data['contact_message_sujet'],
                $data['contact_message_message']
            )
        ) {
            return null;
        }

        $message = new ContactMessageMapping([
            'contact_message_name'
                => $data['contact_message_name'],

            'contact_message_email'
                => $data['contact_message_email'],

            'contact_message_sujet'
                => $data['contact_message_sujet'],

            'contact_message_message'
                => $data['contact_message_message'],
        ]);

        // Protection anti-spam
        if (
            $this->hasReachedMessageLimit(
                $message->getContactMessageEmail()
            )
        ) {
            return null;
        }

        $sql = "
            INSERT INTO messageofcontact (
                contact_message_name,
                contact_message_email,
                contact_message_sujet,
                contact_message_message
            )
            VALUES (?, ?, ?, ?)
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $message->getContactMessageName(),
                $message->getContactMessageEmail(),
                $message->getContactMessageSujet(),
                $message->getContactMessageMessage(),
            ]);

            return $this->getMessageById(
                (int) $this->db->lastInsertId()
            );

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Tous les messages
    public function getAllMessages(): array
    {
        $sql = "
            SELECT
                id_contact_message,
                contact_message_name,
                contact_message_email,
                contact_message_sujet,
                contact_message_message,
                contact_message_date

            FROM messageofcontact

            ORDER BY contact_message_date DESC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $messages = [];

            foreach ($rows as $row) {
                $messages[] =
                    new ContactMessageMapping($row);
            }

            return $messages;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Message via son ID
    public function getMessageById(
        int $id
    ): ?ContactMessageMapping {

        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                id_contact_message,
                contact_message_name,
                contact_message_email,
                contact_message_sujet,
                contact_message_message,
                contact_message_date

            FROM messageofcontact

            WHERE id_contact_message = ?

            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$row) {
                return null;
            }

            return new ContactMessageMapping($row);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // Anti-spam :
    // maximum 3 messages en 10 minutes
    // pour une même adresse email
    private function hasReachedMessageLimit(
        string $email
    ): bool {

        $sql = "
            SELECT COUNT(*) AS message_count

            FROM messageofcontact

            WHERE contact_message_email = ?
              AND contact_message_date >=
                  DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return (int) $row['message_count']
                >= self::MAX_MESSAGES;

        } catch (Exception $e) {
            error_log($e->getMessage());

            // En cas d'erreur DB, on bloque l'envoi
            // plutôt que de contourner l'anti-spam.
            return true;
        }
    }
}