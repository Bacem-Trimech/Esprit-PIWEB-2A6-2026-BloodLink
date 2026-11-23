<?php $curRole = (string)($current['role'] ?? 'RESPONSABLE'); ?>
<header class="admin-topbar">
  <div class="topbar-left">
    <button type="button" class="sidebar-toggle-btn" data-toggle-sidebar aria-label="Masquer ou afficher le menu latéral" title="Masquer / afficher le menu latéral">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="3" width="18" height="18" rx="3"/>
        <line x1="9" y1="3" x2="9" y2="21"/>
      </svg>
    </button>
    <span class="eyebrow">BLOODLINK / <?= e(mb_strtoupper(Utilisateur::labelForRole($curRole))) ?></span>
  </div>
  <div class="topbar-user">
    <span class="role-badge role-badge-<?= e(mb_strtolower($curRole)) ?>"><?= e(Utilisateur::labelForRole($curRole)) ?></span>
    <form method="post" action="index.php?page=deconnexion">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <button class="button button-quiet button-small">Déconnexion</button>
    </form>
  </div>
</header>
