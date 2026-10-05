<?php

declare(strict_types=1);

namespace model;

use Exception;

trait StringTrait
{
    /**
     * Transforme un texte en slug.
     *
     * Exemple :
     * "Fondant au chocolat" -> "fondant-au-chocolat"
     */
    public static function slugify(
        string $text,
        bool $prefix = false,
        string $separator = '-'
    ): string {

        $text = preg_replace(
            '~[^\pL\d]+~u',
            $separator,
            $text
        );

        $text = iconv(
            'utf-8',
            'us-ascii//TRANSLIT',
            $text
        );

        $text = preg_replace(
            '~[^-\w]+~',
            '',
            $text
        );

        $text = trim($text, $separator);

        $text = preg_replace(
            '~-+~',
            $separator,
            $text
        );

        $text = strtolower($text);

        if (empty($text)) {
            throw new Exception(
                'Impossible de générer le slug.'
            );
        }

        if ($prefix === true) {
            $text = bin2hex(random_bytes(2))
                . $separator
                . $text;
        }

        return $text;
    }


    /**
     * Coupe un texte sans couper le dernier mot.
     */
    public static function cutTheText(
        string $text,
        int $maxLength = 200
    ): string {

        if (strlen($text) <= $maxLength) {
            return $text;
        }

        $cutText = substr(
            $text,
            0,
            $maxLength
        );

        $lastSpace = strrpos(
            $cutText,
            ' '
        );

        if ($lastSpace !== false) {
            $cutText = substr(
                $cutText,
                0,
                $lastSpace
            );
        }

        return $cutText . '...';
    }
}