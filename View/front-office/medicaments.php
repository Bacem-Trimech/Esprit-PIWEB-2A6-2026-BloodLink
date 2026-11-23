<?php /* Public medicine catalogue. Expects $q, $list (Medicament[]) */ ?>
<section class="page-head"><h2>Medicines</h2><p class="hint">The catalogue of medicines. Use “Find a pharmacy” to see where they are in stock.</p></section>
<div class="wrap wide">
  <form class="searchbar card" method="get" action="index.php">
    <input type="hidden" name="action" value="medicaments">
    <div class="grow"><label for="q">Search</label><input id="q" name="q" type="search" placeholder="Name or active substance" value="<?= e($q) ?>"></div>
    <button class="btn">Search</button>
    <a class="btn out" href="<?= url('pharmacies') ?>">Find a pharmacy</a>
  </form>
  <?php if (!$list): ?><div class="empty reveal"><?= pillIcon() ?><p>No medicine found.</p></div><?php else: ?>
  <div class="pgrid">
    <?php foreach ($list as $m): ?>
    <article class="pcard reveal">
      <div class="pcard-top"><span class="ic" style="--c:#2563eb"><?= pillIcon() ?></span>
        <div><h3><?= e($m->getName()) ?> <?= e((string)$m->getDosage()) ?></h3><div class="hint"><?= e((string)$m->getDci()) ?></div></div></div>
      <div class="pcard-foot"><span class="chip"><?= e($m->getForm()) ?></span>
        <?php if ($m->getRequiresPrescription()): ?><span class="st no">Prescription required</span><?php else: ?><span class="st ok">Without prescription</span><?php endif; ?></div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
