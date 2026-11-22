<?php
require_once __DIR__ . '/../../Controller/PraticienController.php';
require_once __DIR__ . '/../../Model/Praticien.php';

$controller = new PraticienController();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('ID invalide');
}

$p = $controller->recupererPraticien($id);

if (!$p) {
    die('Praticien introuvable');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nom === '' || $prenom === '') {
        $error = 'Nom et prénom obligatoires.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        $obj = new Praticien(
            $id,
            $nom,
            $prenom,
            trim($_POST['specialite'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            $email,
            trim($_POST['adresse'] ?? '')
        );

        $controller->modifierPraticien($obj);

        header('Location: listPraticiens.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier praticien</title>
</head>
<body>
<h2>Modifier un praticien</h2>
<p style="color:red"><?= htmlspecialchars($error) ?></p>

<form method="POST">
    Nom: <input name="nom" value="<?= htmlspecialchars($p['nom']) ?>" required><br>
    Prénom: <input name="prenom" value="<?= htmlspecialchars($p['prenom']) ?>" required><br>
    Spécialité: <input name="specialite" value="<?= htmlspecialchars($p['specialite']) ?>"><br>
    Téléphone: <input name="telephone" value="<?= htmlspecialchars($p['telephone']) ?>"><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($p['email']) ?>"><br>
    Adresse: <input name="adresse" value="<?= htmlspecialchars($p['adresse']) ?>"><br>
    <input type="submit" value="Modifier">
</form>

<a href="listPraticiens.php">Retour</a>
</body>
</html>