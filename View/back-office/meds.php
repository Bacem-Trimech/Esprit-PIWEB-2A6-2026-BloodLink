<?php /* Expects $q, $list (Medicament[]), $can */ ?>
<h2>Medicines</h2>
<form class="toolbar" method="get" action="index.php">
  <input type="hidden" name="action" value="med_list">
  <input type="search" name="q" placeholder="Search name or substance" value="<?= e($q) ?>" aria-label="Search">
  <button class="btn sm">Search</button>
  <?php if ($can('pharmacy.manage')): ?><a class="btn sm out" href="<?= url('med_create') ?>">Add medicine</a><?php endif; ?>
</form>
<div class="card tbl"><table>
  <tr><th>Name</th><th>Active substance</th><th>Form</th><th>Dosage</th><th>Prescription</th><th>Actions</th></tr>
  <?php foreach ($list as $m): ?>
  <tr>
    <td><b><?= e($m->getName()) ?></b></td><td><?= e((string)$m->getDci()) ?></td><td><?= e($m->getForm()) ?></td><td><?= e((string)$m->getDosage()) ?></td>
    <td><?= $m->getRequiresPrescription() ? '<span class="st no">Required</span>' : '<span class="st ok">No</span>' ?></td>
    <td><div class="actions">
      <?php if ($can('pharmacy.manage')): ?>
        <a class="btn sm gray" href="<?= url('med_edit', ['id' => $m->getId()]) ?>">Edit</a>
        <form class="inline" method="post" action="<?= url('med_delete') ?>" data-confirm="Delete this medicine and its stock lines?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m->getId() ?>"><button class="btn sm">Delete</button></form>
      <?php endif; ?></div></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$list): ?><tr><td colspan="6" class="hint">No medicine found.</td></tr><?php endif; ?>
</table></div>
