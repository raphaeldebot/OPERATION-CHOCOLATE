<?php

const DB_DRIVER = "mysql";
const DB_HOST = "localhost";
const DB_LOGIN = "root";
const DB_PWD = "";
const DB_NAME = "maison_rosalie";
const DB_PORT = 3306;
const DB_CHARSET = "utf8mb4";

const MARIA_DSN =
    DB_DRIVER .
    ":host=" . DB_HOST .
    ";dbname=" . DB_NAME .
    ";port=" . DB_PORT .
    ";charset=" . DB_CHARSET .
    ";";

const RACINE_PATH = __DIR__ . "/..";
const RACINE_URL = "http://localhost/OPERATION-CHOCOLATE/public";