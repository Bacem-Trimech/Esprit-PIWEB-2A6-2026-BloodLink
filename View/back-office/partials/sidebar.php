<aside class="sidebar" id="backoffice-sidebar">
  <div class="sidebar-brand-row">
    <a class="brand" href="index.php?page=<?= ($current['role'] ?? '') === 'ADMIN' ? 'tableau-bord' : 'gestion-collectes' ?>">
      <span class="brand-mark">♥</span>
      <span><strong>BloodLink</strong><small><?= ($current['role'] ?? '') === 'ADMIN' ? 'Portail Administrateur' : 'Centre de Collecte' ?></small></span>
    </a>
    <button type="button" class="sidebar-collapse-mini" data-toggle-sidebar aria-label="Replier le menu latéral" title="Replier le menu">«</button>
  </div>
  <?php if (($current['role'] ?? '') === 'ADMIN'): ?>
  <div class="sidebar-label">SUPERVISION & ANALYTIQUE</div>
  <nav class="side-nav">
    <a href="index.php?page=tableau-bord" <?= $page === 'tableau-bord' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">◈</span><span>Tableau de bord & Bilans</span>
    </a>
    <a href="index.php?page=utilisateurs" <?= in_array($page, ['utilisateurs', 'utilisateur-formulaire'], true) ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♙</span><span>Utilisateurs & Rôles</span>
    </a>
    <a href="index.php?page=inviter-responsable" <?= $page === 'inviter-responsable' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">✉</span><span>Accès Responsables</span>
    </a>
    <a href="index.php?page=gestion-sos" <?= $page === 'gestion-sos' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♡</span><span>Modération SOS Sang</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚙</span><span>Mon profil admin</span>
    </a>
    <a class="public-preview" href="index.php?page=collectes">↗ Voir le site public</a>
  </nav>
  <?php else: ?>
  <div class="sidebar-label">GESTION DE CENTRE</div>
  <nav class="side-nav">
    <a href="index.php?page=gestion-collectes" <?= in_array($page, ['gestion-collectes', 'collecte-gestion', 'collecte-formulaire'], true) ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">▦</span><span>Mes collectes & Dons</span>
    </a>
    <a href="index.php?page=scan-donneur" <?= $page === 'scan-donneur' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">▣</span><span>Vérifier QR Donneur</span>
    </a>
    <a href="index.php?page=parametres" <?= $page === 'parametres' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚙</span><span>Configuration du centre</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♙</span><span>Mon compte responsable</span>
    </a>
  </nav>
  <?php endif; ?>
  <div class="sidebar-note">
    <div><span class="status-dot"></span><strong><?= e($current['nom'] ?? '') ?></strong></div>
    <small><?= e($current['email'] ?? '') ?></small>
  </div>
</aside>
