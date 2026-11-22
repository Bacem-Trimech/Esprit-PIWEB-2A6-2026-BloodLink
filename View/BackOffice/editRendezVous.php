<?php
require_once __DIR__ . '/../../Controller/RendezVousController.php';
require_once __DIR__ . '/../../Controller/PraticienController.php';
require_once __DIR__ . '/../../Model/RendezVous.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('ID invalide');
}

$controller = new RendezVousController();
$r = $controller->recupererRendezVous($id);

if (!$r) {
    die('Rendez-vous introuvable');
}

$praticiens = (new PraticienController())->listerPraticiens();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date_rendezvous'] ?? '';
    $heure = $_POST['heure_rendezvous'] ?? '';
    $idp = filter_var($_POST['id_praticien'] ?? '', FILTER_VALIDATE_INT);

    if (!$date || !$heure || !$idp) {
        $error = 'Date, heure et praticien obligatoires.';
    } else {
        $obj = new RendezVous(
            $id,
            $date,
            $heure,
            trim($_POST['motif'] ?? ''),
            $_POST['statut'] ?? 'En attente',
            $idp
        );

        $controller->modifierRendezVous($obj);

        header('Location: listRendezVous.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier rendez-vous</title>
</head>
<body>
<h2>Modifier un rendez-vous</h2>
<p style="color:red"><?= htmlspecialchars($error) ?></p>

<form method="POST">
    Date:
    <input type="date" name="date_rendezvous"
           value="<?= htmlspecialchars($r['date_rendezvous']) ?>" required><br>

    Heure:
    <input type="time" name="heure_rendezvous"
           value="<?= htmlspecialchars($r['heure_rendezvous']) ?>" required><br>

    Motif:
    <textarea name="motif"><?= htmlspecialchars($r['motif']) ?></textarea><br>

    Statut:
    <select name="statut">
        <?php foreach (['En attente', 'Confirmé', 'Annulé'] as $s) { ?>
            <option value="<?= htmlspecialchars($s) ?>"
                <?= $r['statut'] === $s ? 'selected' : '' ?>>
                <?= htmlspecialchars($s) ?>
            </option>
        <?php } ?>
    </select><br>

    Praticien:
    <select name="id_praticien" required>
        <?php foreach ($praticiens as $p) { ?>
            <option value="<?= (int)$p['id_praticien'] ?>"
                <?= (int)$r['id_praticien'] === (int)$p['id_praticien'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['prenom'].' '.$p['nom'].' - '.$p['specialite']) ?>
            </option>
        <?php } ?>
    </select><br>

    <input type="submit" value="Modifier">
</form>

<a href="listRendezVous.php">Retour</a>
</body>
</html>