<?php
/* Back-office layout (sidebar + top bar). head, spinner, footer and scripts are shared with the front office. */
$shared = __DIR__ . '/../front-office/includes';
require $shared . '/head.php';
require $shared . '/spinner.php';
?>
<div class="shell">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <?php require __DIR__ . '/partials/header.php'; ?>
    <div class="body">
<?php if ($f = pull_flash()): ?><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
<?= $content ?>
<?php require $shared . '/footer.php'; ?>
    </div>
  </div>
</div>
<?php require $shared . '/scripts.php'; ?>
