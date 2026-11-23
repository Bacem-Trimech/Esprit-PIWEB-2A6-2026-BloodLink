<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= htmlspecialchars($pageTitle) ?></title><link rel="stylesheet" href="public/assets/css/style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php?action=admin-index">BloodLink <small>Back Office</small></a><a class="btn secondary" href="index.php?action=admin-index">Retour à la gestion</a></header>
<main class="container narrow"><p class="eyebrow">ADMINISTRATION</p><h1><?= htmlspecialchars($pageTitle) ?></h1>
<?php if ($errors): ?><div class="error"><b>Corrige les erreurs :</b><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form id="establishmentForm" class="form-card" method="post" action="<?= htmlspecialchars($formAction) ?>" novalidate><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><div class="grid">
<label>Nom *<input id="nom" name="nom" value="<?= htmlspecialchars($data['nom']) ?>"><small id="nomError"></small></label>
<label>Type *<select id="type" name="type"><option value="">Choisir</option><?php foreach (['Hôpital','Clinique','Centre médical','Centre de santé'] as $t): ?><option value="<?= htmlspecialchars($t) ?>" <?= $data['type']===$t?'selected':'' ?>><?= htmlspecialchars($t) ?></option><?php endforeach; ?></select><small id="typeError"></small></label>
<label>Ville *<input id="ville" name="ville" value="<?= htmlspecialchars($data['ville']) ?>"><small id="villeError"></small></label>
<label>Adresse *<input id="adresse" name="adresse" value="<?= htmlspecialchars($data['adresse']) ?>"><small id="adresseError"></small></label>
<label>Téléphone *<input id="telephone" name="telephone" value="<?= htmlspecialchars($data['telephone']) ?>"><small id="telephoneError"></small></label>
<label>Email *<input id="email" name="email" type="email" value="<?= htmlspecialchars($data['email']) ?>"><small id="emailError"></small></label>
<label class="wide">Description<textarea id="description" name="description" rows="4"><?= htmlspecialchars($data['description']) ?></textarea></label></div>
<p class="hint">* Champs obligatoires. Les contrôles de saisie sont effectués en JavaScript.</p><div class="actions"><a class="btn secondary" href="index.php?action=admin-index">Annuler</a><button class="btn" type="submit">Enregistrer</button></div></form></main><script src="public/assets/js/app.js"></script></body></html>