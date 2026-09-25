<?php

// paramètres de connexion
const DB_DRIVER = "mysql";
const DB_HOST = "localhost";
const DB_LOGIN = "root";
const DB_PWD = "";
const DB_NAME = "maison_rosalie";
const DB_PORT = 3307;
const DB_CHARSET = "utf8mb4";

// DSN pour la connexion PDO
const MARIA_DSN =
    DB_DRIVER .
    ":host=" . DB_HOST .
    ";dbname=" . DB_NAME .
    ";port=" . DB_PORT .
    ";charset=" . DB_CHARSET .
    ";";

// URL absolue du projet
const URL_BASE = __DIR__;