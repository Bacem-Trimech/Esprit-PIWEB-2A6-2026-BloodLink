<?php
// Démonstrateur provisoire : données conservées uniquement dans la session PHP.
// À remplacer par la connexion commune de l'équipe lors de l'intégration.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

if (!isset($_SESSION['etablissements'])) {
    $_SESSION['etablissements'] = [
        ['id'=>1,'nom'=>'Hôpital de démonstration','type'=>'Hôpital','ville'=>'Tunis','adresse'=>'Tunis, Tunisie','telephone'=>'71000000','email'=>'contact@example.tn','description'=>'Établissement de démonstration.'],
        ['id'=>2,'nom'=>'Clinique de démonstration','type'=>'Clinique','ville'=>'Ariana','adresse'=>'Ariana, Tunisie','telephone'=>'72000000','email'=>'clinique@example.tn','description'=>'Deuxième établissement de démonstration.']
    ];
}

// Identifiants de démonstration uniquement. À remplacer par l'authentification
// commune de l'équipe avant toute utilisation réelle.
define('DEMO_ADMIN_USERNAME', 'admin');
define('DEMO_ADMIN_PASSWORD', 'BloodLink2026!');
