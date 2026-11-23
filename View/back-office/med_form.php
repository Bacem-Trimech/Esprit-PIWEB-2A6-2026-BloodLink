<?php /* Expects $isNew, $old, $errors, $forms */ ?>
<h2><?= $isNew ? 'Add medicine' : 'Edit medicine' ?></h2>
<div class="card" style="max-width:640px;margin-top:16px">
  <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
  <form method="post" action="<?= $isNew ? url('med_create') : url('med_edit', ['id' => (int)($_GET['id'] ?? 0)]) ?>" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="g2">
      <div><label for="name">Name</label><input id="name" name="name" data-rule="required" value="<?= e($old['name']) ?>"></div>
      <div><label for="dci">Active substance (optional)</label><input id="dci" name="dci" value="<?= e($old['dci']) ?>"></div>
    </div>
    <div class="g2">
      <div><label for="form">Form</label><select id="form" name="form"><?php foreach ($forms as $f): ?><option <?= $old['form'] === $f ? 'selected' : '' ?>><?= e($f) ?></option><?php endforeach; ?></select></div>
      <div><label for="dosage">Dosage (optional)</label><input id="dosage" name="dosage" placeholder="500 mg" value="<?= e($old['dosage']) ?>"></div>
    </div>
    <label class="check-row"><input type="checkbox" name="requires_prescription" <?= $old['rx'] ? 'checked' : '' ?>> Requires a prescription</label>
    <div class="actions" style="margin-top:20px"><button class="btn"><?= $isNew ? 'Add medicine' : 'Save changes' ?></button><a class="btn gray" href="<?= url('med_list') ?>">Cancel</a></div>
  </form>
</div>
