<?php if ($page==='accueil'): ?>
<section class="hero"><div class="hero-copy"><span class="eyebrow">LE LIEN QUI SAUVE</span><h1>Un don de sang.<br><em>Des vies sauvées.</em></h1><p><?= e($settings['message_accueil']??'Découvrez les collectes à venir, réservez votre créneau et retrouvez vos dons confirmés.') ?></p><div class="hero-actions"><a class="button button-primary" href="index.php?page=collectes">Trouver une collecte →</a><a class="button button-outline" href="index.php?page=sos">Alertes SOS</a></div><div class="trust-row"><span>Réservation en ligne</span><span>Accueil sur place</span><span>Historique des dons</span></div></div><div class="hero-art"><div class="hero-glow"><div class="blood-drop">♥</div></div><div class="drop-card left"><span class="mini-icon">♡</span><b>Chaque don compte</b><span>Un geste simple pour aider.</span></div><div class="drop-card right"><span class="mini-icon">◷</span><b>Choisissez un créneau</b><span>Selon les places disponibles.</span></div></div></section>
<section class="section">
  <div class="section-heading"><div><span class="eyebrow">PROCHAINEMENT</span><h2>Collectes en cours et à venir</h2></div><a class="text-link" href="index.php?page=collectes">Filtrer toutes les collectes →</a></div>
  <div class="collectes-showcase-grid">
    <?php foreach (array_slice($items,0,3) as $c):
      $aDonneIci = !empty($eligibilite['collectes_donnees'][(int)$c['id']]);
      $imgSrc = !empty($c['image_url']) ? (string)$c['image_url'] : 'public/assets/img/collecte-centre.svg';
      $nbCreneaux = (int)($c['nb_creneaux'] ?? 0);
      $placesLibres = (int)($c['places_libres'] ?? 0);
      $dStart = date('Y-m-d', strtotime($c['debut']));
      $dEnd = date('Y-m-d', strtotime($c['fin']));
      $enCours = strtotime($c['debut']) <= time() && strtotime($c['fin']) >= time();
    ?>
    <article class="collecte-showcase-card">
      <a class="collecte-media-link" href="index.php?page=collecte&id=<?= (int)$c['id'] ?>">
        <img class="collecte-cover-img" src="<?= e($imgSrc) ?>" alt="<?= e($c['titre']) ?>">
        <div class="collecte-media-overlay">
          <div class="collecte-overlay-top">
            <span class="collecte-city-pill">📍 <?= e($c['ville']) ?></span>
            <?php if ($aDonneIci): ?>
              <span class="badge-inline badge-ok">✓ Vous avez donné ici</span>
            <?php elseif ($enCours): ?>
              <span class="collecte-status-pill">● En cours</span>
            <?php else: ?>
              <span class="collecte-status-pill">📅 Programmée</span>
            <?php endif; ?>
          </div>
          <div class="collecte-date-chip">
            <?php if ($dStart !== $dEnd): ?>
              📅 Du <?= e(date('d/m/Y', strtotime($c['debut']))) ?> au <?= e(date('d/m/Y', strtotime($c['fin']))) ?> · <?= e(date('H:i', strtotime($c['debut']))) ?>–<?= e(date('H:i', strtotime($c['fin']))) ?>
            <?php else: ?>
              📅 <?= e(date('d/m/Y', strtotime($c['debut']))) ?> · <?= e(date('H:i', strtotime($c['debut']))) ?> – <?= e(date('H:i', strtotime($c['fin']))) ?> (24h)
            <?php endif; ?>
          </div>
        </div>
      </a>
      <div class="collecte-showcase-body">
        <span class="collecte-org-tag"><?= e($c['nom_organisation'] ?: 'Centre BloodLink') ?></span>
        <h3><a href="index.php?page=collecte&id=<?= (int)$c['id'] ?>"><?= e($c['titre']) ?></a></h3>
        <p class="collecte-address-line"><?= e($c['adresse']) ?>, <?= e($c['ville']) ?></p>
        <div class="collecte-showcase-footer">
          <div class="collecte-slots-meta">
            <?php if ($nbCreneaux > 0): ?>
              <strong><?= $placesLibres ?> place(s) libre(s)</strong>
              <small><?= $nbCreneaux ?> créneau(x) horaire(s)</small>
            <?php else: ?>
              <strong>Accueil sur place</strong>
              <small>Horaires 24h : <?= e(date('H:i', strtotime($c['debut']))) ?> – <?= e(date('H:i', strtotime($c['fin']))) ?></small>
            <?php endif; ?>
          </div>
          <a class="button button-primary button-small" href="index.php?page=collecte&id=<?= (int)$c['id'] ?>">Voir & Réserver →</a>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<?php elseif ($page==='collectes'): ?>
<div class="page-wrap">
  <div class="page-intro">
    <div>
      <span class="eyebrow">CALENDRIER DES CAMPAGNES DE DON (HORLOGE 24H)</span>
      <h1>Collectes de sang en cours et à venir</h1>
      <p>Filtrez les collectes longue durée ou programmées dans le futur selon vos dates et votre tranche horaire (ex. entre 10:00 et 12:00).</p>
    </div>
  </div>
  <?php if (!empty($eligibilite['en_delai_12_semaines'])): ?>
  <div class="cooldown-banner">
    <div>
      <span class="eyebrow">DÉLAI DE RÉCUPÉRATION DE 12 SEMAINES</span>
      <h3>Votre dernier don date du <?= e(date('d/m/Y', strtotime((string)$eligibilite['dernier_don_le']))) ?> (<?= e((string)$eligibilite['dernier_don_titre']) ?>)</h3>
      <p>Par sécurité médicale, vous ne pouvez pas réserver une collecte ayant lieu avant le <strong><?= e(date('d/m/Y', strtotime((string)$eligibilite['prochain_don_le']))) ?></strong>.</p>
    </div>
    <div class="cooldown-timer-box">
      <small>Temps restant avant prochain don</small>
      <strong class="cooldown-timer" data-cooldown-until="<?= e((string)$eligibilite['prochain_don_iso']) ?>"><?= (int)$eligibilite['jours_restants'] ?> jour(s)</strong>
    </div>
  </div>
  <?php endif; ?>

  <!-- Barre de filtrage par Dates (Du / Au) et Horaires 24h (ex. entre 10:00 et 12:00) -->
  <section class="filter-bar-panel" data-collecte-filter-root="#public-collectes-grid [data-filter-item]">
    <div class="filter-bar-grid">
      <div class="field">
        <label for="pub-f-q">Ville, centre ou titre</label>
        <input id="pub-f-q" type="search" data-filter-query placeholder="Ex. Tunis, Ariana, Centre Démo…">
      </div>
      <div class="field">
        <label for="pub-f-d1">Date : Du</label>
        <input id="pub-f-d1" type="date" data-filter-date-from>
      </div>
      <div class="field">
        <label for="pub-f-d2">Au (inclus)</label>
        <input id="pub-f-d2" type="date" data-filter-date-to>
      </div>
      <div class="field">
        <label for="pub-f-t1">Horaire souhaité (24h) : De</label>
        <select id="pub-f-t1" data-filter-time-from>
          <option value="">Toute heure</option>
          <?php for ($h = 6; $h <= 22; $h++): $hm = sprintf('%02d:00', $h); ?>
            <option value="<?= $hm ?>"><?= $hm ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="field">
        <label for="pub-f-t2">À (format 24h)</label>
        <select id="pub-f-t2" data-filter-time-to>
          <option value="">Toute heure</option>
          <?php for ($h = 7; $h <= 23; $h++): $hm = sprintf('%02d:00', $h); ?>
            <option value="<?= $hm ?>"><?= $hm ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="filter-bar-actions">
        <button type="button" class="button button-quiet button-small" data-filter-reset>Réinitialiser</button>
        <span class="filter-count-pill" data-filter-count><?= count($items) ?> collecte(s)</span>
      </div>
    </div>
  </section>

  <div class="collectes-showcase-grid" id="public-collectes-grid">
    <?php foreach ($items as $c):
      $aDonneIci = !empty($eligibilite['collectes_donnees'][(int)$c['id']]);
      $imgSrc = !empty($c['image_url']) ? (string)$c['image_url'] : 'public/assets/img/collecte-centre.svg';
      $nbCreneaux = (int)($c['nb_creneaux'] ?? 0);
      $placesLibres = (int)($c['places_libres'] ?? 0);
      $dStart = date('Y-m-d', strtotime($c['debut']));
      $dEnd = date('Y-m-d', strtotime($c['fin']));
      $tStart = date('H:i', strtotime($c['debut']));
      $tEnd = date('H:i', strtotime($c['fin']));
      $enCours = strtotime($c['debut']) <= time() && strtotime($c['fin']) >= time();
      $multiDay = ($dStart !== $dEnd);
    ?>
    <article class="collecte-showcase-card"
             data-filter-item
             data-start-date="<?= e($dStart) ?>"
             data-end-date="<?= e($dEnd) ?>"
             data-start-time="<?= e($tStart) ?>"
             data-end-time="<?= e($tEnd) ?>"
             data-slot-times="<?= e((string)($c['horaires_resume'] ?? '')) ?>">
      <a class="collecte-media-link" href="index.php?page=collecte&id=<?= (int)$c['id'] ?>">
        <img class="collecte-cover-img" src="<?= e($imgSrc) ?>" alt="<?= e($c['titre']) ?>">
        <div class="collecte-media-overlay">
          <div class="collecte-overlay-top">
            <span class="collecte-city-pill">📍 <?= e($c['ville']) ?></span>
            <?php if ($aDonneIci): ?>
              <span class="badge-inline badge-ok">✓ Vous avez donné ici</span>
            <?php elseif ($enCours): ?>
              <span class="collecte-status-pill">● En cours<?= $multiDay ? ' (Longue durée)' : '' ?></span>
            <?php else: ?>
              <span class="collecte-status-pill">📅 Programmée<?= $multiDay ? ' (Plusieurs jours)' : '' ?></span>
            <?php endif; ?>
          </div>
          <div class="collecte-date-chip">
            <?php if ($multiDay): ?>
              📅 Du <?= e(date('d/m/Y', strtotime($c['debut']))) ?> au <?= e(date('d/m/Y', strtotime($c['fin']))) ?> · <?= e($tStart) ?> – <?= e($tEnd) ?> (24h)
            <?php else: ?>
              📅 <?= e(date('d/m/Y', strtotime($c['debut']))) ?> · <?= e($tStart) ?> – <?= e($tEnd) ?> (24h)
            <?php endif; ?>
          </div>
        </div>
      </a>
      <div class="collecte-showcase-body">
        <span class="collecte-org-tag">ORGANISÉ PAR <?= e(mb_strtoupper((string)($c['nom_organisation'] ?: 'BloodLink'))) ?></span>
        <h2><a href="index.php?page=collecte&id=<?= (int)$c['id'] ?>"><?= e($c['titre']) ?></a></h2>
        <p class="collecte-address-line">🏢 <?= e($c['adresse']) ?>, <?= e($c['ville']) ?></p>
        <?php if (!empty($c['message_accueil'])): ?>
          <p class="collecte-welcome-snippet"><?= e($c['message_accueil']) ?></p>
        <?php endif; ?>
        <div class="collecte-showcase-footer">
          <div class="collecte-slots-meta">
            <?php if ($nbCreneaux > 0): ?>
              <strong><?= $placesLibres ?> place(s) disponible(s)</strong>
              <small>Réparties sur <?= $nbCreneaux ?> créneau(x) (<?= e($tStart) ?>–<?= e($tEnd) ?>)</small>
            <?php else: ?>
              <strong>Accueil sur place ouvert</strong>
              <small>Horaires 24h : <?= e($tStart) ?> à <?= e($tEnd) ?></small>
            <?php endif; ?>
          </div>
          <a class="button button-primary button-small" href="index.php?page=collecte&id=<?= (int)$c['id'] ?>">Choisir un créneau →</a>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if (!$items): ?><div class="empty-state"><h2>Aucune collecte à venir</h2><p>Revenez bientôt.</p></div><?php endif; ?>
</div>

<?php elseif ($page==='collecte' && $detail):
  $aDonneDansCetteCollecte = !empty($eligibilite['collectes_donnees'][(int)$detail['id']]);
  $bloqueParDelai12s = !empty($eligibilite['prochain_don_le']) && strtotime($detail['debut']) < strtotime((string)$eligibilite['prochain_don_le']);
  $heroImg = !empty($detail['image_url']) ? (string)$detail['image_url'] : 'public/assets/img/collecte-centre.svg';
  $galeriePhotos = !empty($detail['galerie']) && is_array($detail['galerie'])
      ? array_slice($detail['galerie'], 0, CollecteSang::MAX_GALERIE_IMAGES)
      : [$heroImg, 'public/assets/img/collecte-mobile.svg', 'public/assets/img/collecte-campus.svg'];
  $totalPlacesLibres = 0;
  foreach ($slots as $sl) {
      if ($sl['statut'] === 'OUVERT') {
          $totalPlacesLibres += max(0, (int)$sl['capacite'] - (int)$sl['places_prises']);
      }
  }
  $dStart = date('Y-m-d', strtotime($detail['debut']));
  $dEnd = date('Y-m-d', strtotime($detail['fin']));
  $multiDay = ($dStart !== $dEnd);
?>
<div class="page-wrap">
  <a class="back-link" href="index.php?page=collectes">← Retour à toutes les collectes</a>
  <?php foreach ($errors as $err): ?><p class="notice"><?= e($err) ?></p><?php endforeach; ?>
  <?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?>

  <?php if ($aDonneDansCetteCollecte): ?>
  <div class="cooldown-banner cooldown-banner-done">
    <div>
      <span class="eyebrow">DON DÉJÀ EFFECTUÉ</span>
      <h3>✓ Vous avez déjà effectué un don lors de cette collecte</h3>
      <p>Don enregistré le <strong><?= e(date('d/m/Y à H:i', strtotime((string)$eligibilite['collectes_donnees'][(int)$detail['id']]))) ?></strong>. Merci pour votre engagement !</p>
    </div>
    <?php if (!empty($eligibilite['en_delai_12_semaines'])): ?>
    <div class="cooldown-timer-box">
      <small>Prochain don possible dans</small>
      <strong class="cooldown-timer" data-cooldown-until="<?= e((string)$eligibilite['prochain_don_iso']) ?>"><?= (int)$eligibilite['jours_restants'] ?> jour(s)</strong>
    </div>
    <?php endif; ?>
  </div>
  <?php elseif ($bloqueParDelai12s): ?>
  <div class="cooldown-banner">
    <div>
      <span class="eyebrow">RÉSERVATION BLOQUÉE · DÉLAI DE 12 SEMAINES</span>
      <h3>Cette collecte a lieu avant la fin de votre délai de récupération</h3>
      <p>Votre dernier don a été effectué le <strong><?= e(date('d/m/Y', strtotime((string)$eligibilite['dernier_don_le']))) ?></strong>. Vous pourrez participer à une collecte à partir du <strong><?= e(date('d/m/Y', strtotime((string)$eligibilite['prochain_don_le']))) ?></strong>.</p>
    </div>
    <div class="cooldown-timer-box">
      <small>Déblocage dans</small>
      <strong class="cooldown-timer" data-cooldown-until="<?= e((string)$eligibilite['prochain_don_iso']) ?>"><?= (int)$eligibilite['jours_restants'] ?> jour(s)</strong>
    </div>
  </div>
  <?php endif; ?>

  <!-- Bannière Héro de la Collecte : Galerie interactive (jusqu'à 4 photos) À GAUCHE + Détails À DROITE -->
  <article class="collecte-hero-card">
    <div class="collecte-hero-gallery-left">
      <div class="hero-main-photo-frame">
        <img id="hero-main-photo" src="<?= e($galeriePhotos[0]) ?>" alt="<?= e($detail['titre']) ?>">
        <span class="hero-photo-counter"><?= count($galeriePhotos) ?> photo(s) du lieu</span>
      </div>
      <div class="hero-thumbs-strip" role="region" aria-label="Galerie photos de la collecte">
        <?php foreach ($galeriePhotos as $idx => $gUrl): ?>
          <button type="button" class="hero-thumb-btn <?= $idx === 0 ? 'active' : '' ?>" data-gallery-thumb="<?= e($gUrl) ?>" data-gallery-target="#hero-main-photo" aria-label="Voir photo <?= $idx + 1 ?>">
            <img src="<?= e($gUrl) ?>" alt="Miniature <?= $idx + 1 ?>">
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="collecte-hero-content">
      <div class="collecte-hero-badges">
        <span class="pill">● Collecte publiée</span>
        <span class="sos-city-tag">📍 <?= e($detail['ville']) ?></span>
        <?php if ($detail['nom_organisation']): ?>
          <span class="badge-inline badge-neutral">🏥 <?= e($detail['nom_organisation']) ?></span>
        <?php endif; ?>
      </div>
      <h1><?= e($detail['titre']) ?></h1>
      <p class="collecte-hero-address"><strong>Lieu d’accueil :</strong> <?= e($detail['adresse']) ?>, <?= e($detail['ville']) ?></p>
      <?php if (!empty($detail['message_accueil'])): ?>
        <div class="collecte-hero-welcome">
          <strong>Consignes du centre aux donneurs :</strong>
          <p><?= e($detail['message_accueil']) ?></p>
        </div>
      <?php endif; ?>
      <div class="collecte-hero-facts">
        <div class="hero-fact-tile">
          <small>📅 PÉRIODE DE COLLECTE</small>
          <strong><?= $multiDay ? 'Du ' . e(date('d/m/Y',strtotime($detail['debut']))) . ' au ' . e(date('d/m/Y',strtotime($detail['fin']))) : e(date('d/m/Y',strtotime($detail['debut']))) ?></strong>
        </div>
        <div class="hero-fact-tile">
          <small>⏰ HORAIRES (FORMAT 24H)</small>
          <strong><?= e(date('H:i',strtotime($detail['debut']))) ?> – <?= e(date('H:i',strtotime($detail['fin']))) ?></strong>
        </div>
        <div class="hero-fact-tile">
          <small>💺 DISPONIBILITÉ EN LIGNE</small>
          <strong><?= count($slots) > 0 ? $totalPlacesLibres . ' place(s) sur ' . count($slots) . ' créneau(x)' : 'Accueil direct sur place' ?></strong>
        </div>
      </div>
      <?php if (!empty($detail['contact_public'])): ?>
        <p class="collecte-hero-contact">Une question ? Contactez l’équipe organisatrice : <a href="mailto:<?= e($detail['contact_public']) ?>"><?= e($detail['contact_public']) ?></a></p>
      <?php endif; ?>
    </div>
  </article>

  <!-- Section des Créneaux de réservation avec filtre horaire 24h -->
  <section class="panel slots-booking-panel" data-collecte-filter-root="#detail-slots-grid [data-filter-item]">
    <div class="panel-heading">
      <div>
        <span class="eyebrow">PRISE DE RENDEZ-VOUS (HORLOGE 24H)</span>
        <h2>Créneaux horaires disponibles</h2>
        <p class="muted">Choisissez ou filtrez votre tranche horaire en format 24h (ex. entre 10:00 et 12:00).</p>
      </div>
      <?php if (count($slots) > 0): ?>
        <div class="slots-inline-filter">
          <label>Entre
            <select data-filter-time-from aria-label="Heure de début (24h)">
              <option value="">Toute heure</option>
              <?php for ($h = 6; $h <= 22; $h++): $hm = sprintf('%02d:00', $h); ?>
                <option value="<?= $hm ?>"><?= $hm ?></option>
              <?php endfor; ?>
            </select>
          </label>
          <label>et
            <select data-filter-time-to aria-label="Heure de fin (24h)">
              <option value="">Toute heure</option>
              <?php for ($h = 7; $h <= 23; $h++): $hm = sprintf('%02d:00', $h); ?>
                <option value="<?= $hm ?>"><?= $hm ?></option>
              <?php endfor; ?>
            </select>
          </label>
          <button type="button" class="button button-quiet button-small" data-filter-reset>Tous</button>
          <span class="pill" data-filter-count><?= count($slots) ?> créneau(x)</span>
        </div>
      <?php endif; ?>
    </div>

    <?php if (count($slots) > 0): ?>
      <div class="slot-cards-grid" id="detail-slots-grid">
        <?php foreach ($slots as $s):
          $cap = max(1, (int)$s['capacite']);
          $prises = (int)$s['places_prises'];
          $libres = max(0, $cap - $prises);
          $pctOcc = min(100, round(($prises / $cap) * 100));
          $estComplet = ($libres <= 0 || $s['statut'] !== 'OUVERT');
          $sDate = date('Y-m-d', strtotime($s['debut']));
          $sStart = date('H:i', strtotime($s['debut']));
          $sEnd = date('H:i', strtotime($s['fin']));
        ?>
        <article class="slot-booking-card <?= $estComplet ? 'slot-card-full' : '' ?>"
                 data-filter-item
                 data-start-date="<?= e($sDate) ?>"
                 data-end-date="<?= e($sDate) ?>"
                 data-start-time="<?= e($sStart) ?>"
                 data-end-time="<?= e($sEnd) ?>">
          <div class="slot-card-top">
            <div class="slot-time-badge">
              <span>⏰ Créneau 24h</span>
              <strong><?= e($sStart) ?> – <?= e($sEnd) ?></strong>
            </div>
            <?php if ($estComplet): ?>
              <span class="badge-inline badge-danger">Complet</span>
            <?php else: ?>
              <span class="badge-inline badge-ok"><?= $libres ?> / <?= $cap ?> libre(s)</span>
            <?php endif; ?>
          </div>
          <div class="slot-progress-bar">
            <div style="width:<?= $pctOcc ?>%"></div>
          </div>
          <div class="slot-card-bottom">
            <small><?= e(date('d/m/Y', strtotime($s['debut']))) ?></small>
            <?php if ($current && $current['role']==='DONNEUR' && !$estComplet): ?>
              <?php if ($aDonneDansCetteCollecte): ?>
                <span class="badge-inline badge-ok">✓ Don effectué ici</span>
              <?php elseif ($bloqueParDelai12s): ?>
                <span class="badge-inline badge-warn">⏳ Délai 12 sem.</span>
              <?php else: ?>
                <form method="post" action="index.php?page=reserver">
                  <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                  <input type="hidden" name="creneau_id" value="<?= (int)$s['id'] ?>">
                  <button class="button button-primary button-small">Réserver ce créneau</button>
                </form>
              <?php endif; ?>
            <?php elseif (!$current && !$estComplet): ?>
              <a class="button button-outline button-small" href="index.php?page=connexion">Se connecter pour réserver</a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="walkin-welcome-card">
        <div class="walkin-icon">♥</div>
        <div>
          <h3>Accueil direct sur place sans rendez-vous</h3>
          <p>Cette collecte accueille les donneurs directement sur place le <strong><?= e(date('d/m/Y', strtotime($detail['debut']))) ?></strong> entre <strong><?= e(date('H:i', strtotime($detail['debut']))) ?> et <?= e(date('H:i', strtotime($detail['fin']))) ?> (format 24h)</strong> à l’adresse <strong><?= e($detail['adresse']) ?>, <?= e($detail['ville']) ?></strong>.</p>
        </div>
        <?php if ($current && $current['role']==='DONNEUR'): ?>
          <a class="button button-outline" href="index.php?page=mon-qr">▣ Afficher mon QR donneur</a>
        <?php elseif (!$current): ?>
          <a class="button button-primary" href="index.php?page=connexion">Se connecter</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php elseif ($page==='mes-participations'): ?>
<div class="page-wrap">
  <div class="page-intro">
    <div>
      <span class="eyebrow">MON ESPACE DONNEUR</span>
      <h1>Mes réservations et mes dons</h1>
      <p>Retrouvez votre historique de prélèvements, vos codes de réservation et votre compteur d’éligibilité de 12 semaines.</p>
    </div>
  </div>
  <?php if (!empty($eligibilite)): ?>
    <?php if ($eligibilite['statut_centre'] === 'NON_AUTORISE'): ?>
      <div class="cooldown-banner cooldown-banner-danger">
        <div>
          <span class="eyebrow">AVIS DU CENTRE DE TRANSFUSION</span>
          <h3>Nouvelle réservation non autorisée actuellement</h3>
          <p>Selon le dernier suivi enregistré par le centre, une nouvelle inscription n’est pas ouverte. Veuillez contacter le centre pour plus d’informations.</p>
        </div>
      </div>
    <?php elseif ($eligibilite['en_delai_12_semaines']): ?>
      <div class="cooldown-banner">
        <div>
          <span class="eyebrow">COMPTEUR D’ÉLIGIBILITÉ · DÉLAI DE 12 SEMAINES</span>
          <h3>Merci pour votre don du <?= e(date('d/m/Y à H:i', strtotime((string)$eligibilite['dernier_don_le']))) ?> (<?= e((string)$eligibilite['dernier_don_titre']) ?>)</h3>
          <p>Vous avez cumulé <strong><?= (int)$eligibilite['total_dons'] ?> don(s) (<?= number_format((int)$eligibilite['volume_total_ml'], 0, ',', ' ') ?> ml)</strong>. Votre prochaine réservation est possible pour toute collecte à partir du <strong><?= e(date('d/m/Y', strtotime((string)$eligibilite['prochain_don_le']))) ?></strong>.</p>
        </div>
        <div class="cooldown-timer-box">
          <small>Temps restant avant prochain don</small>
          <strong class="cooldown-timer" data-cooldown-until="<?= e((string)$eligibilite['prochain_don_iso']) ?>"><?= (int)$eligibilite['jours_restants'] ?> jour(s)</strong>
        </div>
      </div>
    <?php elseif ($eligibilite['total_dons'] > 0): ?>
      <div class="cooldown-banner cooldown-banner-done">
        <div>
          <span class="eyebrow">ÉLIGIBILITÉ AU DON</span>
          <h3>✓ Vous êtes éligible pour réserver une nouvelle collecte</h3>
          <p>Votre délai de 12 semaines depuis votre dernier don (<?= e(date('d/m/Y', strtotime((string)$eligibilite['dernier_don_le']))) ?>) est écoulé. Total donné : <strong><?= (int)$eligibilite['total_dons'] ?> don(s) · <?= number_format((int)$eligibilite['volume_total_ml'], 0, ',', ' ') ?> ml</strong>.</p>
        </div>
        <a class="button button-primary" href="index.php?page=collectes">Réserver un créneau →</a>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Collecte</th><th>Mode & Code</th><th>Créneau (24h)</th><th>Statut</th><th>Arrivée (24h)</th><th>Don confirmé & Délai</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($participations as $p):
          $finDelaiDon = $p['effectue_le'] ? strtotime($p['effectue_le'] . ' +12 weeks') : null;
          $donEnDelai = $finDelaiDon && $finDelaiDon > time();
        ?>
        <tr>
          <td>
            <strong><?= e($p['titre']) ?></strong>
            <small><?= e($p['ville']) ?> · <?= e($p['adresse']) ?></small>
          </td>
          <td>
            <span class="pill <?= $p['mode']==='SUR_PLACE'?'draft':'' ?>"><?= $p['mode']==='SUR_PLACE'?'Sur place':'En ligne' ?></span>
            <small>Code : <code><?= e($p['code_confirmation']) ?></code></small>
          </td>
          <td><?= $p['creneau_debut'] ? e(date('d/m/Y H:i',strtotime($p['creneau_debut']))) : 'Accueil sur place' ?></td>
          <td><?= e($p['statut']) ?></td>
          <td><?= $p['arrivee_le'] ? e(date('d/m/Y H:i',strtotime($p['arrivee_le']))) : '—' ?></td>
          <td>
            <?php if ($p['effectue_le']): ?>
              <span class="badge-inline badge-ok">✓ A donné ici (<?= (int)$p['volume_ml'] ?> ml)</span>
              <small><?= e(date('d/m/Y à H:i',strtotime($p['effectue_le']))) ?> · Poche <code><?= e($p['numero_poche']) ?></code></small>
              <?php if ($donEnDelai): ?>
                <small class="cooldown-note">⏳ Délai 12 sem. actif jusqu’au <?= e(date('d/m/Y', $finDelaiDon)) ?></small>
              <?php endif; ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td>
            <?php if ($p['statut']==='CONFIRMEE' && strtotime($p['collecte_debut'])>time()): ?>
              <form method="post" action="index.php?page=annuler"><input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="button button-quiet button-small">Annuler</button></form>
            <?php endif; ?>
            <a class="text-link" href="index.php?page=mes-examens">Examens & Résultats →</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!$participations): ?><div class="empty-state">Vous n’avez pas encore de réservation ou de don.</div><?php endif; ?>
</div>

<?php elseif ($page==='activer'): ?>
<div class="page-wrap"><div class="form-panel"><span class="eyebrow">INVITATION</span><h2>Finaliser mon compte</h2><?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?><p>Ce lien est personnel, utilisable une seule fois et expire après 30 jours.</p><form method="post" action="index.php?page=activer" data-validate="activation"><input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="jeton" value="<?= e((string)($_GET['jeton'] ?? $_POST['jeton'] ?? '')) ?>"><div class="form-grid"><div class="field"><label for="telephone">Téléphone</label><input id="telephone" name="telephone" value="<?= e($_POST['telephone'] ?? '') ?>"></div><div class="field"><label for="ville">Ville</label><input id="ville" name="ville" value="<?= e($_POST['ville'] ?? '') ?>"></div><div class="field"><label for="date_naissance">Date de naissance</label><input id="date_naissance" name="date_naissance" type="date" value="<?= e($_POST['date_naissance'] ?? '') ?>"></div><div class="field"><label for="mot_de_passe">Mot de passe (12 caractères minimum)</label><input id="mot_de_passe" name="mot_de_passe" type="password"></div><div class="field"><label for="confirmation">Confirmer</label><input id="confirmation" name="confirmation" type="password"></div></div><div class="form-actions"><button class="button button-primary">Activer mon compte</button></div></form></div></div>

<?php elseif ($page==='connexion' || $page==='inscription'): ?>
<div class="page-wrap"><div class="form-panel"><span class="eyebrow">MON COMPTE</span><h2><?= $page==='connexion' ? 'Connexion' : 'Créer un compte donneur' ?></h2><?php if (!empty($_SESSION['inscription_reussie'])): unset($_SESSION['inscription_reussie']); ?><p class="notice" role="alert">Compte créé ! Connectez-vous pour continuer la configuration de votre profil.</p><?php endif; ?><?php if (!empty($_SESSION['activation_reussie'])): unset($_SESSION['activation_reussie']); ?><p class="notice" role="status">Compte activé. Vous pouvez vous connecter.</p><?php endif; ?><?php foreach ($errors as $err): ?><p class="notice"><?= e($err) ?></p><?php endforeach; ?><?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?><form method="post" action="index.php?page=<?= e($page) ?>" data-validate="auth"><input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><div class="form-grid"><?php if ($page==='inscription'): ?><div class="field full"><label for="nom">Nom complet</label><input id="nom" name="nom" value="<?= e($_POST['nom'] ?? '') ?>"></div><?php endif; ?><div class="field full"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="field full"><label for="mot_de_passe">Mot de passe</label><input id="mot_de_passe" name="mot_de_passe" type="password"></div><?php if ($page==='inscription'): ?><div class="field full"><label for="confirmation">Confirmer le mot de passe</label><input id="confirmation" name="confirmation" type="password"></div><?php endif; ?></div><div class="form-actions"><button class="button button-primary"><?= $page==='connexion' ? 'Se connecter' : 'Créer mon compte' ?></button></div></form><p class="form-note"><?= $page==='connexion' ? '<a href="index.php?page=inscription">Créer un compte</a>' : '<a href="index.php?page=connexion">Déjà inscrit ? Se connecter</a>' ?></p></div></div>

<?php elseif (in_array($page,['profil','mon-qr','mes-examens','sos','sos-nouveau'],true)): require __DIR__.'/extra.php'; ?>
<?php else: ?><div class="page-wrap"><div class="empty-state"><h1><?= $page==='introuvable' ? 'Page introuvable' : 'Une erreur est survenue' ?></h1><p><?= e($message) ?></p><a class="button button-primary" href="index.php">Retour à l’accueil</a></div></div><?php endif; ?>
