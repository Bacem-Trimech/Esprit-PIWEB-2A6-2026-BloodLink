<?php /* Expects $isNew, $old, $errors, $pharmacies (EtablissementSante[]) */ ?>
<h2><?= $isNew ? 'Add duty shift' : 'Edit duty shift' ?></h2>
<div class="card" style="max-width:640px;margin-top:16px">
  <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
  <form method="post" action="<?= $isNew ? url('garde_create') : url('garde_edit', ['id' => (int)($_GET['id'] ?? 0)]) ?>" novalidate>
    <?= csrf_field() ?>
    <label for="etablissement_id">Pharmacy</label>
    <select id="etablissement_id" name="etablissement_id"><?php foreach ($pharmacies as $p): ?><option value="<?= $p->getId() ?>" <?= (int)$old['etab'] === $p->getId() ? 'selected' : '' ?>><?= e($p->getName()) ?> (<?= e($p->getCity()) ?>)</option><?php endforeach; ?></select>
    <div class="g2">
      <div><label for="start_date">Start date</label><input type="date" id="start_date" name="start_date" value="<?= e($old['start']) ?>"></div>
      <div><label for="end_date">End date</label><input type="date" id="end_date" name="end_date" value="<?= e($old['end']) ?>"></div>
    </div>
    <label for="notes">Notes (optional)</label><input id="notes" name="notes" maxlength="200" value="<?= e($old['notes']) ?>">
    <div class="actions" style="margin-top:20px"><button class="btn"><?= $isNew ? 'Add duty shift' : 'Save changes' ?></button><a class="btn gray" href="<?= url('garde_list') ?>">Cancel</a></div>
  </form>
</div>
