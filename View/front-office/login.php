<?php /* Expects $errors (array), $old (array) */ ?>
<div class="wrap"><div class="card">
  <div class="tabs"><a class="active" href="<?= url('login') ?>">Log in</a><a href="<?= url('register') ?>">Sign up</a></div>
  <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
  <form method="post" action="<?= url('login') ?>" data-validate novalidate>
    <?= csrf_field() ?>
    <label for="email">Email address</label>
    <input type="email" id="email" name="email" data-rule="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" data-rule="required" autocomplete="current-password">
    <button class="btn" style="width:100%;margin-top:18px">Log in</button>
  </form>
  <?php if (DEMO_MODE): ?>
  <div class="quick">
    <p class="hint">Demo mode: click a role to fill the form automatically, then press Log in.</p>
    <div class="chips">
      <?php foreach ([['Donor', 'donor@demo.tn', '#16a34a'], ['Support', 'support@demo.tn', '#0f766e'], ['Moderator', 'moderateur@demo.tn', '#f59e0b'],
                      ['Admin gestion', 'gestion@demo.tn', '#2563eb'], ['Admin supervision', 'supervision@demo.tn', '#9333ea'], ['Super admin', 'super@demo.tn', '#dc2626']] as [$lbl, $mail, $col]): ?>
        <button type="button" class="qbtn" style="--c:<?= $col ?>" data-fill-email="<?= e($mail) ?>" data-fill-pw="<?= e(DEMO_PASSWORD) ?>"><i></i><?= e($lbl) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div></div>
