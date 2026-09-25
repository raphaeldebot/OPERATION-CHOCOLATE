<?php
//stagiaires/Meidhy/02-connection-with-consent/index.php

// chargement des dépendances 
require_once 'config-dev.php';

// on veut se connecter (essai)
try{
    //tentative de connexion 
    $db = new PDO(
        //équivaut à MySQL:
        dsn: DB_CONNECT_TYPE.":" .
        // host=localhost
        "host=".DB_CONNECT_HOST .
        // ; dbname =
        ";dbname=".DB_CONNECT_NAME .
        // ; port:3307
        ";port=".DB_CONNECT_PORT . 
        // ; charset=utf8mb4;
        ";charset=".DB_CONNECT_CHARSET . ";" 
        ,
        username: DB_CONNECT_USER,
        password: "",
        );
// en cas d'erreur (équivalent $e = new PDOexception)
}catch(PDOException $e){
    //on récupère et on l'affiche 
    echo "Erreur numéro : ".$e->getcode(); // le code
    echo "<br>Message de l'erreur : ".$e->getmessage(); // le message 
}
