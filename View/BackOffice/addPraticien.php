<?php
require_once __DIR__ . '/../../Controller/PraticienController.php';
require_once __DIR__ . '/../../Model/Praticien.php';

$error = '';

if (isset($_POST['nom'], $_POST['prenom'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email'] ?? '');

    if ($nom === '' || $prenom === '') {
        $error = 'Le nom et le prénom sont obligatoires.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $praticien = new Praticien(
            null,
            $nom,
            $prenom,
            trim($_POST['specialite'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            $email,
            trim($_POST['adresse'] ?? '')
        );

        $controller = new PraticienController();
        $controller->ajouterPraticien($praticien);

        header('Location: listPraticiens.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter praticien</title>
</head>
<body>
<h2>Ajouter un praticien</h2>
<p style="color:red"><?= htmlspecialchars($error) ?></p>

<form method="POST">
    Nom: <input type="text" name="nom" required><br>
    Prénom: <input type="text" name="prenom" required><br>
    Spécialité: <input type="text" name="specialite"><br>
    Téléphone: <input type="text" name="telephone"><br>
    Email: <input type="email" name="email"><br>
    Adresse: <input type="text" name="adresse"><br>
    <input type="submit" value="Ajouter">
</form>

<a href="listPraticiens.php">Retour</a>
</body>
</html>