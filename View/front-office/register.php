<?php /* Expects $errors (array), $old (array) */ ?>
<div class="wrap"><div class="card">
  <div class="tabs"><a href="<?= url('login') ?>">Log in</a><a class="active" href="<?= url('register') ?>">Sign up</a></div>
  <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
  <form method="post" action="<?= url('register') ?>" data-validate novalidate>
    <?= csrf_field() ?>
    <label for="username">Username</label>
    <input type="text" id="username" name="username" data-rule="username" value="<?= e($old['username'] ?? '') ?>" autocomplete="username">
    <label for="email">Email address</label>
    <input type="email" id="email" name="email" data-rule="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
    <div class="g2">
      <div><label for="password">Password (min. 8 characters)</label><input type="password" id="password" name="password" data-rule="password" autocomplete="new-password"></div>
      <div><label for="password_confirm">Confirm password</label><input type="password" id="password_confirm" name="password_confirm" data-rule="required" data-match="#password" autocomplete="new-password"></div>
    </div>
    <div class="g2">
      <div><label for="birth_date">Date of birth</label><input type="date" id="birth_date" name="birth_date" data-rule="adult" max="<?= date('Y-m-d') ?>" value="<?= e($old['birth_date'] ?? '') ?>"></div>
      <div><label for="city">City</label><input type="text" id="city" name="city" data-rule="city" value="<?= e($old['city'] ?? '') ?>"></div>
    </div>
    <p class="hint">You must be 18 or older to donate.</p>
    <button class="btn" style="width:100%">Create my account</button>
  </form>
</div></div>
