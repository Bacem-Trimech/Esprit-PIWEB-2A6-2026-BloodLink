<header class="admin-topbar">
  <div>
    <span class="eyebrow">BLOODLINK / <?= ($current['role'] ?? '') === 'ADMIN' ? 'DIRECTION & STATISTIQUES' : 'OPÉRATIONS DE COLLECTE' ?></span>
  </div>
  <div class="topbar-user">
    <span class="role-badge role-badge-<?= e(mb_strtolower((string)($current['role'] ?? 'responsable'))) ?>"><?= ($current['role'] ?? '') === 'ADMIN' ? 'Administrateur' : 'Responsable de collecte' ?></span>
    <form method="post" action="index.php?page=deconnexion">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <button class="button button-quiet button-small">Déconnexion</button>
    </form>
  </div>
</header>
