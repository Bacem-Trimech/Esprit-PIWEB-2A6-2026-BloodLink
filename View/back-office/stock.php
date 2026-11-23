<?php /* Expects $list (Disponibilite[]), $pharmacies, $meds, $canManage */ ?>
<h2>Stock</h2>
<p class="hint">Quantity of each medicine declared by each pharmacy (0 = out of stock).</p>
<?php if ($canManage): ?>
<form class="toolbar card" method="post" action="<?= url('stock_save') ?>" style="margin-top:16px">
  <?= csrf_field() ?>
  <select name="etablissement_id" aria-label="Pharmacy"><?php foreach ($pharmacies as $p): ?><option value="<?= $p->getId() ?>"><?= e($p->getName()) ?></option><?php endforeach; ?></select>
  <select name="medicament_id" aria-label="Medicine"><?php foreach ($meds as $m): ?><option value="<?= $m->getId() ?>"><?= e($m->getName() . ' ' . $m->getDosage()) ?></option><?php endforeach; ?></select>
  <input type="number" name="quantity" min="0" max="100000" value="10" aria-label="Quantity" style="min-width:110px">
  <button class="btn sm">Save stock</button>
</form>
<?php endif; ?>
<div class="card tbl" style="margin-top:16px"><table>
  <tr><th>Pharmacy</th><th>Medicine</th><th>Quantity</th><th>Updated</th><th>Actions</th></tr>
  <?php foreach ($list as $d): ?>
  <tr>
    <td><?= e($d->getEtablissementName()) ?></td><td><?= e($d->getMedicamentName()) ?></td>
    <td><?php if ($canManage): ?>
      <form class="inline" method="post" action="<?= url('stock_save') ?>"><?= csrf_field() ?><input type="hidden" name="etablissement_id" value="<?= $d->getEtablissementId() ?>"><input type="hidden" name="medicament_id" value="<?= $d->getMedicamentId() ?>">
        <input type="number" name="quantity" min="0" max="100000" value="<?= $d->getQuantity() ?>" aria-label="Quantity" style="width:90px"> <button class="btn sm gray">Update</button></form>
      <?php else: ?><?= $d->getQuantity() ?><?php endif; ?></td>
    <td><?= e($d->getUpdatedAt()) ?></td>
    <td><?php if ($canManage): ?><form class="inline" method="post" action="<?= url('stock_delete') ?>" data-confirm="Delete this stock line?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $d->getId() ?>"><button class="btn sm">Delete</button></form><?php endif; ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$list): ?><tr><td colspan="5" class="hint">No stock declared yet.</td></tr><?php endif; ?>
</table></div>
