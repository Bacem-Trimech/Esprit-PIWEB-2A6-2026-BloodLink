<?php /* Expects $rows (user + permitted actions), $q, $role, $roles (Role[]), $can */ ?>
<h2>Users</h2>
<form class="toolbar" method="get" action="index.php">
  <input type="hidden" name="action" value="users">
  <input type="search" name="q" placeholder="Search username or email" value="<?= e($q) ?>" aria-label="Search">
  <select name="role" aria-label="Filter by role">
    <option value="">All roles</option>
    <?php foreach ($roles as $r): ?><option value="<?= e($r->getCode()) ?>" <?= $role === $r->getCode() ? 'selected' : '' ?>><?= e($r->getLabel()) ?></option><?php endforeach; ?>
  </select>
  <button class="btn sm">Filter</button>
  <?php if ($can('users.create')): ?><a class="btn sm out" href="<?= url('user_create') ?>">Add user</a><?php endif; ?>
</form>
<div class="card tbl">
  <table>
    <tr><th>ID</th><th>User</th><th>Role</th><th>Status</th><th>City</th><th>Created</th><th>Actions</th></tr>
    <?php foreach ($rows as $row): $u = $row['user']; ?>
    <tr>
      <td><?= $u->getId() ?></td>
      <td><b><?= e($u->getUsername()) ?></b><br><span class="hint"><?= e($u->getEmail()) ?></span></td>
      <td><span class="chip"><?= e($u->getRoleLabel()) ?></span></td>
      <td><span class="st <?= $u->getStatus() === 'active' ? 'ok' : 'no' ?>"><?= e(ucfirst($u->getStatus())) ?></span></td>
      <td><?= e($u->getCity() ?? '') ?></td>
      <td><?= e($u->getCreatedAt()) ?></td>
      <td><div class="actions">
        <?php if ($row['edit']): ?><a class="btn sm gray" href="<?= url('user_edit', ['id' => $u->getId()]) ?>">Edit</a><?php endif; ?>
        <?php if ($row['status']): ?>
          <form class="inline" method="post" action="<?= url('user_status') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u->getId() ?>"><button class="btn sm gray"><?= $u->getStatus() === 'active' ? 'Suspend' : 'Reactivate' ?></button></form>
        <?php endif; ?>
        <?php if ($row['delete']): ?>
          <form class="inline" method="post" action="<?= url('user_delete') ?>" data-confirm="Delete this user permanently?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u->getId() ?>"><button class="btn sm">Delete</button></form>
        <?php endif; ?>
      </div></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="hint">No user found.</td></tr><?php endif; ?>
  </table>
</div>
