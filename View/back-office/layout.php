<!DOCTYPE html>
<html lang="fr">
<head>
  <?php require_once __DIR__ . '/partials/head.php'; ?>
  <?php require_once __DIR__ . '/partials/scripts.php'; ?>
</head>
<body class="admin-shell role-<?= e(mb_strtolower((string)($current['role'] ?? 'responsable'))) ?>">
  <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
  <div class="admin-main">
    <?php require_once __DIR__ . '/partials/header.php'; ?>
    <main class="page-content"><?= $content ?></main>
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
  </div>
</body>
</html>
