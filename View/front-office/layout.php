<?php
/* Public layout. Receives $title, $content, $user, $can from BaseController::render(). */
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/spinner.php';
require __DIR__ . '/includes/navbar.php';
?>
<main>
<?php if ($f = pull_flash()): ?><div class="wrap" style="margin-bottom:0"><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div></div><?php endif; ?>
<?= $content ?>
</main>
<?php
require __DIR__ . '/includes/footer.php';
require __DIR__ . '/includes/scripts.php';
