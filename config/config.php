<?php

function connexion()
{
    $pdo = new PDO(
        "mysql:host=localhost;dbname=bloodlink",
        "root",
        ""
    );

    return $pdo;
}
