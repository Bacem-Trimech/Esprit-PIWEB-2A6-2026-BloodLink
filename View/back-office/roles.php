<?php /* Expects $roles (Role[]), $byRole (code => count). Permissions come from config.php */ ?>
<h2>Roles</h2>
<p class="hint">Roles are stored in the database; what each role may do is defined in config.php.</p>
<div class="card tbl" style="margin-top:16px">
  <table><tr><th>Role</th><th>Space</th><th>Users</th><th>Permissions</th><th>Can manage</th></tr>
  <?php foreach ($roles as $r): ?>
    <tr>
      <td><b><?= e($r->getLabel()) ?></b><br><span class="hint"><?= e($r->getCode()) ?></span></td>
      <td><?= $r->getSide() === 'back' ? 'Back office' : 'Front office' ?></td>
      <td><?= (int)($byRole[$r->getCode()] ?? 0) ?></td>
      <td><?= e(implode(', ', PERMISSIONS[$r->getCode()] ?? [])) ?></td>
      <td><?= e(implode(', ', MANAGEABLE[$r->getCode()] ?? [])) ?: '<span class="hint">nobody</span>' ?></td>
    </tr>
  <?php endforeach; ?></table>
</div>
