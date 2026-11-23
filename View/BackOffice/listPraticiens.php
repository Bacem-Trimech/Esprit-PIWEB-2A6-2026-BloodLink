<?php
require_once __DIR__ . '/../../Controller/PraticienController.php';

$controller = new PraticienController();
$liste = $controller->listerPraticiens();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des praticiens</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="../../public/assets/js/main.js" defer></script>
</head>
<body>
<main class="container">
<h2>Liste des praticiens</h2>

<a class="btn" href="addPraticien.php">Ajouter un praticien</a>

<table class="table">
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Spécialité</th>
    <th>Téléphone</th>
    <th>Email</th>
    <th>Adresse</th>
    <th>Actions</th>
</tr>

<?php foreach ($liste as $p) { ?>
<tr>
    <td><?= (int)$p['id_praticien'] ?></td>
    <td><?= htmlspecialchars($p['nom']) ?></td>
    <td><?= htmlspecialchars($p['prenom']) ?></td>
    <td><?= htmlspecialchars($p['specialite']) ?></td>
    <td><?= htmlspecialchars($p['telephone']) ?></td>
    <td><?= htmlspecialchars($p['email']) ?></td>
    <td><?= htmlspecialchars($p['adresse']) ?></td>
    <td>
        <a href="editPraticien.php?id=<?= (int)$p['id_praticien'] ?>">Modifier</a>
        <a href="deletePraticien.php?id=<?= (int)$p['id_praticien'] ?>"
           data-confirm="Supprimer ce praticien ?">Supprimer</a>
    </td>
</tr>
<?php } ?>
</table>
</main>
</body>
</html>