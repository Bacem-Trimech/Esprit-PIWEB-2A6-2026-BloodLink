<?php
require_once __DIR__ . '/../../Controller/RendezVousController.php';
require_once __DIR__ . '/../../Controller/PraticienController.php';
require_once __DIR__ . '/../../Model/RendezVous.php';

$pc = new PraticienController();
$praticiens = $pc->listerPraticiens();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date_rendezvous'] ?? '';
    $heure = $_POST['heure_rendezvous'] ?? '';
    $idp = filter_var($_POST['id_praticien'] ?? '', FILTER_VALIDATE_INT);

    if (!$date || !$heure || !$idp) {
        $error = 'Veuillez remplir la date, l’heure et le praticien.';
    } else {
        $rdv = new RendezVous(
            null,
            $date,
            $heure,
            trim($_POST['motif'] ?? ''),
            $_POST['statut'] ?? 'En attente',
            $idp
        );

        $controller = new RendezVousController();
        $controller->ajouterRendezVous($rdv);

        header('Location: listRendezVous.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="../../public/assets/js/main.js" defer></script>
</head>
<body>
<main class="container">
<h2>Ajouter un rendez-vous</h2>
<p class="error"><?= htmlspecialchars($error) ?></p>

<form method="POST" class="form" novalidate>
    Date: <input type="date" name="date_rendezvous" required><br>
    Heure: <input type="time" name="heure_rendezvous" required><br>
    Motif: <textarea name="motif"></textarea><br>

    Statut:
    <select name="statut">
        <option>En attente</option>
        <option>Confirmé</option>
        <option>Annulé</option>
    </select><br>

    Praticien:
    <select name="id_praticien" required>
        <option value="">Choisir</option>
        <?php foreach ($praticiens as $p) { ?>
            <option value="<?= (int)$p['id_praticien'] ?>">
                <?= htmlspecialchars($p['prenom'].' '.$p['nom'].' - '.$p['specialite']) ?>
            </option>
        <?php } ?>
    </select><br>

    <input type="submit" value="Ajouter">
</form>

<a class="btn btn-secondary" href="listRendezVous.php">Retour</a>
</main>
</body>
</html>