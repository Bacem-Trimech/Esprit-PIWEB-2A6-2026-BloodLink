<?php /* Back-office sidebar: only the entries the role may use. Needs $user and $can. */
$current = $_GET['action'] ?? 'admin';
$alias = ['user_edit' => 'users', 'med_create' => 'med_list', 'med_edit' => 'med_list', 'garde_create' => 'garde_list', 'garde_edit' => 'garde_list'];
$current = $alias[$current] ?? $current;
$items = [
    ['Dashboard', 'admin', 'backoffice', 'Overview'],
    ['Users', 'users', 'users.view', 'Accounts'], ['Add user', 'user_create', 'users.create', 'Accounts'], ['Roles', 'roles', 'users.view', 'Accounts'],
    ['Medicines', 'med_list', 'pharmacy.view', 'Pharmacies'], ['Duty shifts', 'garde_list', 'pharmacy.view', 'Pharmacies'], ['Stock', 'stock_list', 'pharmacy.view', 'Pharmacies'],
]; $last = ''; ?>
<aside class="side">
  <div class="logo">
    <button class="heart" type="button" aria-label="Switch between day and night mode" title="Switch between day and night mode"><?= heartIcon() ?></button>
    <a href="<?= url('home') ?>"><span>BloodLink<small><?= e($user->getRoleLabel()) ?></small></span></a>
  </div>
  <nav class="nav" aria-label="Menu">
    <?php foreach ($items as [$label, $act, $perm, $section]): if (!$can($perm)) continue;
        if ($section !== $last): $last = $section; ?><div class="nav-title"><?= e($section) ?></div><?php endif; ?>
      <a class="<?= $current === $act ? 'active' : '' ?>" href="<?= url($act) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
</aside>
