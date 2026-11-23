<section class="hero">
  <span class="pill"><span class="dot"></span>Live donation network</span>
  <h1 class="big">Donate blood.<br><em>Save lives.</em><br>Instantly.</h1>
  <p class="lead">Find a collection center, book your visit and answer emergency SOS alerts in a few taps.</p>
  <div class="cta">
    <a class="btn" href="<?= url('register') ?>">I want to donate</a>
    <a class="btn out" href="<?= url('login') ?>">Log in</a>
  </div>
  <div class="trust"><span>Verified centers</span><span>Emergency SOS alerts</span><span>Appointment booking</span><span>Secure accounts</span></div>
</section>
<section class="features">
  <a class="feature reveal" href="<?= url('pharmacies') ?>"><span class="ic" style="--c:#dc2626"><?= pinIcon() ?></span><h3>Pharmacies on duty</h3><p>See which pharmacies are open tonight, by city.</p></a>
  <a class="feature reveal" href="<?= url('pharmacies', ['q' => 'Doliprane', 'searched' => 1, 'duty' => 1]) ?>"><span class="ic" style="--c:#2563eb"><?= pillIcon() ?></span><h3>Find a medicine</h3><p>Check which on-duty pharmacy has it in stock.</p></a>
  <a class="feature reveal" href="<?= url('register') ?>"><span class="ic" style="--c:#16a34a"><?= heartIcon() ?></span><h3>Give blood</h3><p>Create a donor account and help save lives.</p></a>
</section>
