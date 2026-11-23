<?php /* Donor space. Expects $user, $errors (array), $old (array), $form ('profile' | 'password' | '') */
$val = fn(string $k, ?string $fallback) => e($old[$k] ?? $fallback ?? ''); ?>
<div class="wrap" style="max-width:760px">
  <h2>Welcome, <?= e($user->getUsername()) ?></h2>
  <p><span class="chip"><?= e($user->getRoleLabel()) ?></span> <span class="hint">Member since <?= e($user->getCreatedAt()) ?></span></p>

  <div class="card" style="margin-top:16px">
    <b>My profile</b>
    <?php if ($errors && $form === 'profile'): ?><div class="alert alert-error" style="margin-top:10px"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
    <form method="post" action="<?= url('dashboard') ?>" data-validate novalidate>
      <?= csrf_field() ?><input type="hidden" name="form" value="profile">
      <div class="g2">
        <div><label for="username">Username</label><input type="text" id="username" name="username" data-rule="username" value="<?= $val('username', $user->getUsername()) ?>"></div>
        <div><label for="email">Email address</label><input type="email" id="email" name="email" data-rule="email" value="<?= $val('email', $user->getEmail()) ?>"></div>
      </div>
      <div class="g2">
        <div><label for="birth_date">Date of birth</label><input type="date" id="birth_date" name="birth_date" data-rule="adult" max="<?= date('Y-m-d') ?>" value="<?= $val('birth_date', $user->getBirthDate()) ?>"></div>
        <div><label for="city">City</label><input type="text" id="city" name="city" data-rule="city" value="<?= $val('city', $user->getCity()) ?>"></div>
      </div>
      <button class="btn" style="margin-top:16px">Save changes</button>
    </form>
  </div>

  <div class="card" style="margin-top:16px">
    <b>Change password</b>
    <?php if ($errors && $form === 'password'): ?><div class="alert alert-error" style="margin-top:10px"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
    <form method="post" action="<?= url('dashboard') ?>" data-validate novalidate>
      <?= csrf_field() ?><input type="hidden" name="form" value="password">
      <label for="current_password">Current password</label><input type="password" id="current_password" name="current_password" data-rule="required" autocomplete="current-password">
      <div class="g2">
        <div><label for="new_password">New password (min. 8)</label><input type="password" id="new_password" name="new_password" data-rule="password" autocomplete="new-password"></div>
        <div><label for="new_password_confirm">Confirm new password</label><input type="password" id="new_password_confirm" name="new_password_confirm" data-rule="required" data-match="#new_password" autocomplete="new-password"></div>
      </div>
      <button class="btn gray" style="margin-top:16px">Change password</button>
    </form>
  </div>
</div>
