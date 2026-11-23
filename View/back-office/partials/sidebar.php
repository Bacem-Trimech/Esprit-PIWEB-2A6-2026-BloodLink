<?php
$curRole = (string)($current['role'] ?? 'RESPONSABLE');
$homeTarget = Utilisateur::homePageForRole($curRole);
$brandSub = match ($curRole) {
    'ADMIN' => 'Portail Administrateur',
    'RESPONSABLE_ANNUAIRE' => 'Gestion Annuaire Médical',
    'RESPONSABLE_RDV' => 'Gestion Rendez-vous',
    'RESPONSABLE_PHARMACIE' => 'Gestion Pharmacie',
    'RESPONSABLE_LABO' => 'Gestion Laboratoire',
    default => 'Centre de Collecte',
};
?>
<aside class="sidebar" id="backoffice-sidebar">
  <div class="sidebar-brand-row">
    <a class="brand" href="index.php?page=<?= e($homeTarget) ?>">
      <span class="brand-mark">♥</span>
      <span><strong>BloodLink</strong><small><?= e($brandSub) ?></small></span>
    </a>
    <button type="button" class="sidebar-collapse-mini" data-toggle-sidebar aria-label="Replier le menu latéral" title="Replier le menu">«</button>
  </div>
  <?php if ($curRole === 'ADMIN'): ?>
  <div class="sidebar-label">SUPERVISION & BLOODLINK</div>
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
  </nav>
  <div class="sidebar-label" style="margin-top:14px">MODULES SANTÉ INTÉGRÉS</div>
  <nav class="side-nav">
    <a href="index.php?page=admin-annuaire" <?= $page === 'admin-annuaire' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">✚</span><span>Annuaire & Services</span>
    </a>
    <a href="index.php?page=admin-rendez-vous" <?= $page === 'admin-rendez-vous' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">◷</span><span>Praticiens & RDV</span>
    </a>
    <a href="index.php?page=admin-pharmacies" <?= $page === 'admin-pharmacies' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚕</span><span>Pharmacies & Gardes</span>
    </a>
    <a href="index.php?page=admin-laboratoires" <?= $page === 'admin-laboratoires' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚗</span><span>Analyses & Labo</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚙</span><span>Mon profil admin</span>
    </a>
    <a class="public-preview" href="index.php?page=collectes">↗ Voir le site public</a>
  </nav>
  <?php elseif ($curRole === 'RESPONSABLE'): ?>
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
    <a class="public-preview" href="index.php?page=collectes">↗ Voir le portail public</a>
  </nav>
  <?php elseif ($curRole === 'RESPONSABLE_ANNUAIRE'): ?>
  <div class="sidebar-label">ESPACE ANNUAIRE MÉDICAL</div>
  <nav class="side-nav">
    <a href="index.php?page=admin-annuaire" <?= $page === 'admin-annuaire' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">✚</span><span>Établissements & Services</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♙</span><span>Mon compte responsable</span>
    </a>
    <a class="public-preview" href="index.php?page=annuaire">↗ Voir l’annuaire public</a>
  </nav>
  <?php elseif ($curRole === 'RESPONSABLE_RDV'): ?>
  <div class="sidebar-label">ESPACE RENDEZ-VOUS</div>
  <nav class="side-nav">
    <a href="index.php?page=admin-rendez-vous" <?= $page === 'admin-rendez-vous' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">◷</span><span>Praticiens & Rendez-vous</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♙</span><span>Mon compte responsable</span>
    </a>
    <a class="public-preview" href="index.php?page=rendez-vous">↗ Voir les rendez-vous</a>
  </nav>
  <?php elseif ($curRole === 'RESPONSABLE_PHARMACIE'): ?>
  <div class="sidebar-label">ESPACE PHARMACIE</div>
  <nav class="side-nav">
    <a href="index.php?page=admin-pharmacies" <?= $page === 'admin-pharmacies' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚕</span><span>Médicaments, Gardes & Stock</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♙</span><span>Mon compte responsable</span>
    </a>
    <a class="public-preview" href="index.php?page=pharmacies">↗ Voir les pharmacies</a>
  </nav>
  <?php elseif ($curRole === 'RESPONSABLE_LABO'): ?>
  <div class="sidebar-label">ESPACE LABORATOIRE</div>
  <nav class="side-nav">
    <a href="index.php?page=admin-laboratoires" <?= $page === 'admin-laboratoires' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">⚗</span><span>Analyses & Comptes rendus</span>
    </a>
    <a href="index.php?page=profil" <?= $page === 'profil' ? 'aria-current="page"' : '' ?>>
      <span class="nav-icon">♙</span><span>Mon compte responsable</span>
    </a>
    <a class="public-preview" href="index.php?page=laboratoires">↗ Voir les laboratoires</a>
  </nav>
  <?php endif; ?>
  <div class="sidebar-note">
    <div><span class="status-dot"></span><strong><?= e($current['nom'] ?? '') ?></strong></div>
    <small><?= e($current['email'] ?? '') ?></small>
  </div>
</aside>
