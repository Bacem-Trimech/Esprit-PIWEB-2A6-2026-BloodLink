<?php /* Add / edit a user. Expects $target (User|null), $old, $errors, $roles (Role[], empty unless the actor may assign roles) */ ?>
<h2><?= $target ? 'Edit user' : 'Add user' ?></h2>
<div class="card" style="max-width:640px;margin-top:16px">
  <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
  <form method="post" action="<?= $target ? url('user_edit', ['id' => $target->getId()]) : url('user_create') ?>" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="g2">
      <div><label for="username">Username</label><input type="text" id="username" name="username" data-rule="username" value="<?= e($old['username']) ?>"></div>
      <div><label for="email">Email address</label><input type="email" id="email" name="email" data-rule="email" value="<?= e($old['email']) ?>"></div>
    </div>
    <div class="g2">
      <div><label for="birth_date">Date of birth (optional)</label><input type="date" id="birth_date" name="birth_date" data-rule="adult" data-optional max="<?= date('Y-m-d') ?>" value="<?= e($old['birth_date']) ?>"></div>
      <div><label for="city">City (optional)</label><input type="text" id="city" name="city" data-rule="city" value="<?= e($old['city']) ?>"></div>
    </div>
    <label for="password"><?= $target ? 'New password (leave empty to keep the current one)' : 'Password (min. 8 characters)' ?></label>
    <input type="password" id="password" name="password" data-rule="password" <?= $target ? 'data-optional' : '' ?> autocomplete="new-password">
    <label for="role_id">Role</label>
    <?php if ($roles): ?>
      <select id="role_id" name="role_id"><?php foreach ($roles as $r): ?><option value="<?= $r->getId() ?>" <?= (int)$old['role_id'] === $r->getId() ? 'selected' : '' ?>><?= e($r->getLabel()) ?></option><?php endforeach; ?></select>
    <?php else: ?>
      <p class="hint" style="margin:0"><?= $target ? e($target->getRoleLabel()) : 'Donneur' ?> (only a Super admin can choose or change roles)</p>
    <?php endif; ?>
    <div class="actions" style="margin-top:20px"><button class="btn"><?= $target ? 'Save changes' : 'Create user' ?></button><a class="btn gray" href="<?= url('users') ?>">Cancel</a></div>
  </form>
</div>
