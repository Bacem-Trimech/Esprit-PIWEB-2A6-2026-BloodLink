<header class="site-header">
  <a class="brand" href="index.php">
    <span class="brand-mark">♥</span>
    <span><strong>BloodLink</strong><small>Portail Santé & Don de Sang</small></span>
  </a>
  <nav class="main-nav">
    <a href="index.php?page=collectes" <?= in_array($page, ['accueil', 'collectes', 'collecte'], true) ? 'class="active"' : '' ?>>Collectes</a>
    <a href="index.php?page=sos" <?= in_array($page, ['sos', 'sos-nouveau'], true) ? 'class="active"' : '' ?>>SOS sang</a>
    <a href="index.php?page=annuaire" <?= in_array($page, ['annuaire', 'annuaire-detail'], true) ? 'class="active"' : '' ?>>Annuaire</a>
    <a href="index.php?page=rendez-vous" <?= $page === 'rendez-vous' ? 'class="active"' : '' ?>>Rendez-vous</a>
    <a href="index.php?page=pharmacies" <?= $page === 'pharmacies' ? 'class="active"' : '' ?>>Pharmacies</a>
    <a href="index.php?page=laboratoires" <?= $page === 'laboratoires' ? 'class="active"' : '' ?>>Laboratoires</a>
    <?php if ($current && $current['role'] === 'DONNEUR'): ?>
      <a href="index.php?page=mes-participations" <?= in_array($page, ['mes-participations', 'mes-examens', 'mon-qr'], true) ? 'class="active"' : '' ?>>Mes dons</a>
    <?php elseif ($current): ?>
      <a href="index.php?page=<?= e(Utilisateur::homePageForRole((string)$current['role'])) ?>">← Mon espace</a>
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
