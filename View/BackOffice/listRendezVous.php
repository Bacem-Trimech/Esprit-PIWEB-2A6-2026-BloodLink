<?php
require_once __DIR__ . '/../../Controller/RendezVousController.php';

$controller = new RendezVousController();
$liste = $controller->listerRendezVous();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des rendez-vous</title>
</head>
<body>
<h2>Liste des rendez-vous</h2>

<a href="addRendezVous.php">Ajouter un rendez-vous</a>

<table border="1">
<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Heure</th>
    <th>Motif</th>
    <th>Statut</th>
    <th>Praticien</th>
    <th>Spécialité</th>
    <th>Actions</th>
</tr>

<?php foreach ($liste as $r) { ?>
<tr>
    <td><?= (int)$r['id_rendezvous'] ?></td>
    <td><?= htmlspecialchars($r['date_rendezvous']) ?></td>
    <td><?= htmlspecialchars($r['heure_rendezvous']) ?></td>
    <td><?= htmlspecialchars($r['motif']) ?></td>
    <td><?= htmlspecialchars($r['statut']) ?></td>
    <td><?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?></td>
    <td><?= htmlspecialchars($r['specialite']) ?></td>
    <td>
        <a href="editRendezVous.php?id=<?= (int)$r['id_rendezvous'] ?>">Modifier</a>
        <a href="deleteRendezVous.php?id=<?= (int)$r['id_rendezvous'] ?>"
           onclick="return confirm('Supprimer ce rendez-vous ?')">Supprimer</a>
    </td>
</tr>
<?php } ?>
</table>
</body>
</html>