<!DOCTYPE html>
<html lang="fr">
<head>
  <?php require_once __DIR__ . '/includes/head.php'; ?>
  <?php require_once __DIR__ . '/includes/scripts.php'; ?>
</head>
<body class="public-shell">
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>
  <main class="public-main"><?= $content ?></main>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
