<?php /* Public navbar. Needs $user (User or null). The heart switches day/night mode. */
$current = $_GET['action'] ?? 'home'; ?>
<header class="bar">
  <div class="logo">
    <button class="heart" type="button" aria-label="Switch between day and night mode" title="Switch between day and night mode"><?= heartIcon() ?></button>
    <a href="<?= url('home') ?>"><span>BloodLink<small>Blood donation network</small></span></a>
  </div>
  <nav class="menu" aria-label="Main">
    <a class="<?= $current === 'home' ? 'active' : '' ?>" href="<?= url('home') ?>">Home</a>
    <a class="<?= $current === 'pharmacies' ? 'active' : '' ?>" href="<?= url('pharmacies') ?>">Pharmacies</a>
    <a class="<?= $current === 'medicaments' ? 'active' : '' ?>" href="<?= url('medicaments') ?>">Medicines</a>
    <?php if ($user): ?>
      <a href="<?= url($user->getRoleSide() === 'back' ? 'admin' : 'dashboard') ?>"><?= $user->getRoleSide() === 'back' ? 'Back office' : 'My space' ?></a>
      <span class="chip"><?= e($user->getRoleLabel()) ?></span>
      <form class="inline" method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="btn sm">Log out</button></form>
    <?php else: ?>
      <a class="btn" href="<?= url('login') ?>">Login / Sign up</a>
    <?php endif; ?>
  </nav>
</header>
