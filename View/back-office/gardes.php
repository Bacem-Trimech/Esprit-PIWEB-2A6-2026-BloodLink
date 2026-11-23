<?php /* Expects $list (GardePharmacie[]), $today, $can */ ?>
<h2>Duty shifts</h2>
<p class="hint">A pharmacy is “on duty” between its start and end date.</p>
<div class="toolbar"><?php if ($can('pharmacy.manage')): ?><a class="btn sm out" href="<?= url('garde_create') ?>">Add duty shift</a><?php endif; ?></div>
<div class="card tbl"><table>
  <tr><th>Pharmacy</th><th>From</th><th>To</th><th>Status</th><th>Notes</th><th>Actions</th></tr>
  <?php foreach ($list as $g):
      $now = $g->getStartDate() <= $today && $g->getEndDate() >= $today; $future = $g->getStartDate() > $today; ?>
  <tr>
    <td><b><?= e($g->getEtablissementName()) ?></b></td><td><?= e($g->getStartDate()) ?></td><td><?= e($g->getEndDate()) ?></td>
    <td><span class="st <?= $now ? 'ok' : ($future ? '' : 'no') ?>"><?= $now ? 'On duty' : ($future ? 'Upcoming' : 'Past') ?></span></td>
    <td><?= e((string)$g->getNotes()) ?></td>
    <td><div class="actions">
      <?php if ($can('pharmacy.manage')): ?>
        <a class="btn sm gray" href="<?= url('garde_edit', ['id' => $g->getId()]) ?>">Edit</a>
        <form class="inline" method="post" action="<?= url('garde_delete') ?>" data-confirm="Delete this duty shift?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $g->getId() ?>"><button class="btn sm">Delete</button></form>
      <?php endif; ?></div></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$list): ?><tr><td colspan="6" class="hint">No duty shift yet.</td></tr><?php endif; ?>
</table></div>
