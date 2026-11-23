<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Connexion administrateur - BloodLink</title><link rel="stylesheet" href="public/assets/css/style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php">BloodLink <small>Back Office</small></a><a class="btn secondary" href="index.php">Front Office</a></header>
<main class="container narrow"><p class="eyebrow">ESPACE SÉCURISÉ DE DÉMONSTRATION</p><h1>Connexion administrateur</h1><p class="lead">Connecte-toi pour gérer les établissements de santé.</p>
<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form id="adminLoginForm" class="form-card" method="post" action="index.php?action=admin-login" novalidate><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<label>Identifiant<input name="username" id="username" autocomplete="username" required></label><small id="usernameError"></small>
<label>Mot de passe<input name="password" id="password" type="password" autocomplete="current-password" required></label><small id="passwordError"></small>
<div class="actions"><button class="btn" type="submit">Se connecter</button></div>
<p class="hint">Compte de démonstration : admin / BloodLink2026!</p></form></main><script src="public/assets/js/app.js"></script></body></html>