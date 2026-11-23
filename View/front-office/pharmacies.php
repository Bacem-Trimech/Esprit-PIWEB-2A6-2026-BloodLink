<?php /* Public pharmacy search. Expects $q, $city, $onDuty, $results (rows), $cities */ ?>
<section class="page-head">
  <h2>Find a pharmacy</h2>
  <p class="hint">Search pharmacies on duty today and check which ones have your medicine in stock.</p>
</section>
<div class="wrap wide">
  <form class="searchbar card" method="get" action="index.php">
    <input type="hidden" name="action" value="pharmacies"><input type="hidden" name="searched" value="1">
    <div class="grow"><label for="q">Medicine (optional)</label><input id="q" name="q" type="search" placeholder="e.g. Doliprane or Paracétamol" value="<?= e($q) ?>"></div>
    <div><label for="city">City</label>
      <select id="city" name="city"><option value="">All cities</option>
        <?php foreach ($cities as $c): ?><option <?= $city === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
      </select></div>
    <label class="check"><input type="checkbox" name="duty" <?= $onDuty ? 'checked' : '' ?>> On duty today</label>
    <button class="btn">Search</button>
  </form>

  <?php if (!$results): ?>
    <div class="empty reveal"><?= pinIcon() ?><p>No pharmacy found. Try another city, another medicine, or untick “On duty today”.</p></div>
  <?php else: ?>
    <p class="hint"><?= count($results) ?> result<?= count($results) > 1 ? 's' : '' ?></p>
    <div class="pgrid">
      <?php foreach ($results as $r): ?>
      <article class="pcard reveal">
        <div class="pcard-top">
          <span class="ic" style="--c:#dc2626"><?= pinIcon() ?></span>
          <div><h3><?= e($r['name']) ?></h3><div class="hint"><?= e($r['city']) ?>, <?= e($r['address']) ?></div></div>
        </div>
        <?php if (!empty($r['med_name'])): ?>
          <div class="med"><?= pillIcon() ?><span><b><?= e($r['med_name']) ?></b> <?= e(trim(($r['dosage'] ?? '') . ' ' . $r['form'])) ?></span><span class="qty"><?= (int)$r['quantity'] ?> in stock</span></div>
        <?php endif; ?>
        <div class="pcard-foot">
          <?php if (!empty($r['duty_until'])): ?><span class="duty"><span class="pulse"></span>On duty until <?= e(date('d/m/Y', strtotime($r['duty_until']))) ?></span>
          <?php else: ?><span class="off">Not on duty today</span><?php endif; ?>
          <?php if (!empty($r['phone'])): ?><a href="tel:<?= e(preg_replace('/\s+/', '', $r['phone'])) ?>"><?= e($r['phone']) ?></a><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
