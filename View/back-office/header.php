<?php /* Back-office top bar. */ ?>
<div class="top">
  <b><?= e($title) ?></b>
  <div>
    <span class="chip"><?= e($user->getRoleLabel()) ?></span>
    <span class="hint"><?= e($user->getUsername()) ?></span>
    <form class="inline" method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="btn sm gray">Log out</button></form>
  </div>
</div>
