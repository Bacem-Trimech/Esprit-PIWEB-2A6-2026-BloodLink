<header class="site-header">
  <a class="brand" href="index.php">
    <span class="brand-mark">♥</span>
    <span><strong>BloodLink</strong><small>Le lien qui sauve</small></span>
  </a>
  <nav class="main-nav">
    <a href="index.php?page=collectes" <?= in_array($page, ['collectes', 'collecte'], true) ? 'class="active"' : '' ?>>Collectes</a>
    <a href="index.php?page=sos" <?= in_array($page, ['sos', 'sos-nouveau'], true) ? 'class="active"' : '' ?>>SOS sang</a>
    <?php if ($current && $current['role'] === 'DONNEUR'): ?>
      <a href="index.php?page=mes-participations" <?= $page === 'mes-participations' ? 'class="active"' : '' ?>>Mes dons</a>
      <a href="index.php?page=mes-examens" <?= $page === 'mes-examens' ? 'class="active"' : '' ?>>Mes examens</a>
      <a href="index.php?page=mon-qr" <?= $page === 'mon-qr' ? 'class="active"' : '' ?>>Mon QR</a>
    <?php elseif ($current): ?>
      <a href="index.php?page=<?= $current['role'] === 'ADMIN' ? 'tableau-bord' : 'gestion-collectes' ?>">← Retour à mon espace</a>
    <?php endif; ?>
  </nav>
  <div class="header-actions">
    <?php if ($current): ?>
      <a class="account-link" href="index.php?page=profil">
        <span class="avatar" aria-hidden="true" style="background:<?= e($current['avatar_couleur'] ?: ['#D92735', '#3268DC', '#15966A', '#8B5CF6', '#D97706'][(int)$current['id'] % 5]) ?>"><?= e(mb_strtoupper(mb_substr($current['nom'], 0, 1))) ?></span>
        <?= e($current['nom']) ?>
      </a>
      <form method="post" action="index.php?page=deconnexion">
        <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
        <button class="button button-quiet">Déconnexion</button>
      </form>
    <?php else: ?>
      <a class="button button-quiet" href="index.php?page=connexion">Connexion</a>
      <a class="button button-primary" href="index.php?page=inscription">Créer un compte</a>
    <?php endif; ?>
  </div>
</header>
