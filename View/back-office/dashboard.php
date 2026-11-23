<?php /* Expects $total, $byRole (code => count), $roles (Role[]), $recent (User[]) */
$colors = ['#dc2626', '#2563eb', '#9333ea', '#16a34a', '#f59e0b', '#0f766e']; ?>
<h2>Overview</h2>
<p class="hint">Welcome to the BloodLink back office. Account figures come from the database.</p>
<div class="stats">
  <div class="stat"><div><span>Total accounts</span><b><?= (int)$total ?></b><small>All roles</small></div><div class="ic" style="--c:#dc2626"><?= dropIcon() ?></div></div>
  <?php foreach ($roles as $i => $r): ?>
    <div class="stat"><div><span><?= e($r->getLabel()) ?></span><b><?= (int)($byRole[$r->getCode()] ?? 0) ?></b><small><?= $r->getSide() === 'back' ? 'Back office' : 'Front office' ?></small></div><div class="ic" style="--c:<?= $colors[$i % 6] ?>"><?= dropIcon() ?></div></div>
  <?php endforeach; ?>
</div>
<div class="card tbl"><b>Latest accounts</b>
  <table><tr><th>Username</th><th>Email</th><th>Role</th><th>Created</th></tr>
  <?php foreach ($recent as $u): ?>
    <tr><td><?= e($u->getUsername()) ?></td><td><?= e($u->getEmail()) ?></td><td><span class="chip"><?= e($u->getRoleLabel()) ?></span></td><td><?= e($u->getCreatedAt()) ?></td></tr>
  <?php endforeach; ?></table>
</div>
