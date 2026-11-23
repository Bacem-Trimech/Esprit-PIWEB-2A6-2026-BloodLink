<?php
$nbArrivees = 0;
$nbDons = 0;
$volCollecte = 0;
$volSainCollecte = 0;
$volInconnuCollecte = 0;
$groupesCollecteBilan = [];

foreach ($participations as $part) {
    if ($part['statut'] === 'ARRIVEE') $nbArrivees++;
    if (!empty($part['effectue_le'])) {
        $nbDons++;
        $v = (int)($part['volume_ml'] ?? 0);
        $volCollecte += $v;
        $mPart = $medicalMap[$part['id']] ?? [];
        $estSain = (($mPart['resultat_statut'] ?? '') === 'VALIDE')
            && !empty($mPart['groupe_confirme'])
            && ($mPart['vih'] ?? '') === 'NON_REACTIF'
            && ($mPart['vhb'] ?? '') === 'NON_REACTIF'
            && ($mPart['vhc'] ?? '') === 'NON_REACTIF'
            && ($mPart['syphilis'] ?? '') === 'NON_REACTIF';
        $estEcarte = (($mPart['resultat_statut'] ?? '') === 'CONTACT_CENTRE')
            || in_array('A_CONFIRMER', [$mPart['vih'] ?? '', $mPart['vhb'] ?? '', $mPart['vhc'] ?? '', $mPart['syphilis'] ?? ''], true);

        if ($estSain) {
            $volSainCollecte += $v;
            $gKey = (string)$mPart['groupe_confirme'];
            if (!isset($groupesCollecteBilan[$gKey])) {
                $groupesCollecteBilan[$gKey] = ['groupe' => $gKey, 'etat' => 'SAIN', 'volume_ml' => 0, 'poches' => 0];
            }
            $groupesCollecteBilan[$gKey]['volume_ml'] += $v;
            $groupesCollecteBilan[$gKey]['poches']++;
        } elseif ($estEcarte) {
            if (!isset($groupesCollecteBilan['ECARTE'])) {
                $groupesCollecteBilan['ECARTE'] = ['groupe' => 'Écarté (contrôle)', 'etat' => 'ECARTE', 'volume_ml' => 0, 'poches' => 0];
            }
            $groupesCollecteBilan['ECARTE']['volume_ml'] += $v;
            $groupesCollecteBilan['ECARTE']['poches']++;
        } else {
            $volInconnuCollecte += $v;
            if (!isset($groupesCollecteBilan['INCONNU'])) {
                $groupesCollecteBilan['INCONNU'] = ['groupe' => 'Groupe inconnu / En attente labo', 'etat' => 'INCONNU', 'volume_ml' => 0, 'poches' => 0];
            }
            $groupesCollecteBilan['INCONNU']['volume_ml'] += $v;
            $groupesCollecteBilan['INCONNU']['poches']++;
        }
    }
}
$nowLocal = date('Y-m-d\TH:i');
$nextSlotStart = $detail['debut'];
if (!empty($slots)) {
    $lastSlot = end($slots);
    if (!empty($lastSlot['fin']) && strtotime($lastSlot['fin']) < strtotime($detail['fin'])) {
        $nextSlotStart = $lastSlot['fin'];
    }
}
$nextSlotStartInput = date('Y-m-d\TH:i', strtotime($nextSlotStart));
$nextSlotEndTs = min(strtotime($detail['fin']), strtotime($nextSlotStart) + 3600);
if ($nextSlotEndTs <= strtotime($nextSlotStart)) {
    $nextSlotEndTs = strtotime($detail['fin']);
}
$nextSlotEndInput = date('Y-m-d\TH:i', $nextSlotEndTs);
?>
<div class="workspace-header">
  <div class="workspace-title">
    <div class="workspace-badges">
      <span class="pill <?= $detail['statut']==='PUBLIEE'?'':($detail['statut']==='BROUILLON'?'draft':'closed') ?>"><?= e($detail['statut']) ?></span>
      <span class="muted"><?= e($detail['ville']) ?> · <?= e($detail['adresse']) ?></span>
    </div>
    <h2><?= e($detail['titre']) ?></h2>
    <p>Du <strong><?= e(date('d/m/Y à H:i',strtotime($detail['debut']))) ?></strong> au <strong><?= e(date('d/m/Y à H:i',strtotime($detail['fin']))) ?></strong> · <a class="text-link" href="index.php?page=collecte-formulaire&id=<?= (int)$detail['id'] ?>">Modifier les horaires →</a></p>
    <div class="collecte-blood-summary">
      <span><strong>Bilan sanguin de la collecte (<?= number_format($volCollecte, 0, ',', ' ') ?> ml) :</strong></span>
      <?php if (!empty($groupesCollecteBilan)): ?>
        <?php foreach ($groupesCollecteBilan as $gb): ?>
          <?php if ($gb['etat'] === 'SAIN'): ?>
            <span class="blood-chip blood-chip-sain"><strong>✓ <?= e($gb['groupe']) ?> (Sain)</strong> · <?= (int)$gb['volume_ml'] ?> ml (<?= (int)$gb['poches'] ?>)</span>
          <?php elseif ($gb['etat'] === 'ECARTE'): ?>
            <span class="badge-inline badge-danger">✕ Écarté · <?= (int)$gb['volume_ml'] ?> ml</span>
          <?php else: ?>
            <span class="badge-inline badge-warn">⏳ <?= e($gb['groupe']) ?> · <?= (int)$gb['volume_ml'] ?> ml (<?= (int)$gb['poches'] ?>)</span>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <span class="muted">Aucun sang prélevé pour le moment</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="workspace-kpis">
    <div class="wkpi"><span>Inscrits / Venues</span><strong><?= count($participations) ?></strong></div>
    <div class="wkpi"><span>Arrivés</span><strong><?= $nbArrivees ?></strong></div>
    <div class="wkpi"><span>Sain confirmé</span><strong class="text-green"><?= number_format($volSainCollecte, 0, ',', ' ') ?> ml</strong></div>
    <div class="wkpi wkpi-blood"><span>Total prélevé</span><strong><?= number_format($volCollecte, 0, ',', ' ') ?> ml</strong></div>
  </div>
</div>

<?php if (!empty($_SESSION['invitation_lien'])): ?>
<div class="notice invitation-banner" role="status">
  <div>
    <strong>Invitation donneur créée — Compte n° <?= (int)$_SESSION['invitation_id'] ?></strong>
    <p>Vous pouvez immédiatement enregistrer son arrivée avec le numéro <strong><?= (int)$_SESSION['invitation_id'] ?></strong>. Lien d’activation : <a href="<?= e($_SESSION['invitation_lien']) ?>"><?= e($_SESSION['invitation_lien']) ?></a></p>
  </div>
  <div class="invitation-qr" data-link="<?= e($_SESSION['invitation_lien']) ?>"></div>
</div>
<?php unset($_SESSION['invitation_lien'],$_SESSION['invitation_id']); endif; ?>

<div class="workspace-tabs" role="tablist" data-workspace-tabs>
  <button type="button" class="wtab active" role="tab" data-tab-target="tab-accueil" aria-selected="true">
    <span>1 · Accueil & Arrivées</span>
    <b class="wtab-count"><?= count($participations) ?></b>
  </button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-medical" aria-selected="false">
    <span>2 · Examens, Dons & Labo</span>
    <b class="wtab-count"><?= $nbArrivees ?></b>
  </button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-creneaux" aria-selected="false">
    <span>3 · Créneaux & Capacités</span>
    <b class="wtab-count"><?= count($slots) ?></b>
  </button>
</div>

<!-- ONGLET 1 : ACCUEIL & ARRIVÉES -->
<section id="tab-accueil" class="wtab-panel active" role="tabpanel">
  <div class="checkin-toolbar panel">
    <form method="post" action="index.php?page=sur-place" data-validate="sur-place" class="checkin-bar-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <div class="field checkin-field-main">
        <label for="donneur_id">Identifier une arrivée (N° compte, code réservation 12 car. ou QR 48 car.)</label>
        <input id="donneur_id" name="donneur_id" autocomplete="off" placeholder="Ex. 4 ou code de réservation A1B2C3D4E5F6">
      </div>
      <div class="field checkin-field-time">
        <label for="checkin_arrivee_le">Date et heure d’arrivée (modifiable)</label>
        <input id="checkin_arrivee_le" name="arrivee_le" type="datetime-local" value="<?= e($nowLocal) ?>">
      </div>
      <div class="checkin-actions">
        <button class="button button-primary">Enregistrer l’arrivée</button>
        <button type="button" class="button button-outline" data-open-modal="modal-invite-walkin">+ Donneur sans compte</button>
        <a class="button button-quiet" href="index.php?page=scan-donneur">Scanner QR</a>
      </div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading">
      <h2>Liste des participants et statut de don</h2>
      <div class="search-box">
        <input type="search" data-table-search="#part-table" placeholder="Rechercher un participant…">
      </div>
    </div>
    <div class="table-wrap">
      <table class="data-table participation-table" id="part-table">
        <thead>
          <tr>
            <th>Donneur</th>
            <th>Statut du compte & Délai 12 semaines</th>
            <th>Mode & Code</th>
            <th>Arrivée (modifiable)</th>
            <th>Don & Groupe confirmé</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($participations as $p):
            $m = $medicalMap[$p['id']] ?? [];
            $estSainRow = (($m['resultat_statut'] ?? '') === 'VALIDE')
                && !empty($m['groupe_confirme'])
                && ($m['vih'] ?? '') === 'NON_REACTIF'
                && ($m['vhb'] ?? '') === 'NON_REACTIF'
                && ($m['vhc'] ?? '') === 'NON_REACTIF'
                && ($m['syphilis'] ?? '') === 'NON_REACTIF';
          ?>
          <tr data-search-row>
            <td>
              <strong>#<?= (int)$p['donneur_id'] ?> · <?= e($p['nom']) ?></strong>
              <small><?= e($p['email']) ?></small>
            </td>
            <td>
              <div class="status-stack">
                <?php if ($p['effectue_le']): ?>
                  <span class="badge-inline badge-ok">✓ A donné dans cette collecte (<?= (int)$p['volume_ml'] ?> ml)</span>
                <?php endif; ?>
                <?php if ($p['en_delai_12_semaines']): ?>
                  <span class="badge-inline badge-warn">⏳ En délai de 12 sem. jusqu’au <?= e(date('d/m/Y', strtotime((string)$p['prochain_don_possible_le']))) ?> (<?= (int)$p['jours_restants_delai'] ?> j)</span>
                <?php else: ?>
                  <span class="badge-inline badge-neutral">Hors délai de 12 semaines</span>
                <?php endif; ?>
                <small>Historique du compte : <strong><?= (int)$p['total_dons_compte'] ?> don(s) confirmé(s)</strong></small>
              </div>
            </td>
            <td>
              <span class="pill <?= $p['mode']==='SUR_PLACE'?'draft':'' ?>"><?= $p['mode']==='SUR_PLACE'?'Sur place':'Réservation' ?></span>
              <small>Code : <code><?= e($p['code_confirmation']) ?></code></small>
            </td>
            <td>
              <?php if ($p['statut']==='CONFIRMEE'): ?>
                <form method="post" action="index.php?page=arriver" class="inline-time-form">
                  <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                  <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
                  <input type="hidden" name="participation_id" value="<?= (int)$p['id'] ?>">
                  <input type="datetime-local" name="arrivee_le" value="<?= e($nowLocal) ?>" aria-label="Heure d’arrivée">
                  <button class="button button-primary button-small">Marquer arrivé</button>
                </form>
              <?php else: ?>
                <div class="time-display">
                  <strong><?= $p['arrivee_le'] ? e(date('d/m/Y H:i', strtotime($p['arrivee_le']))) : 'Arrivé' ?></strong>
                  <button type="button" class="link-btn" data-open-modal="modal-arrivee-<?= (int)$p['id'] ?>">Modifier l’heure</button>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($p['effectue_le']): ?>
                <strong class="blood-volume-highlight"><?= (int)$p['volume_ml'] ?> ml</strong> ·
                <?php if ($estSainRow): ?>
                  <span class="blood-chip blood-chip-sain"><strong>✓ <?= e($m['groupe_confirme']) ?> (Sain)</strong></span>
                <?php else: ?>
                  <span class="badge-inline badge-warn">⏳ Groupe à confirmer au labo</span>
                <?php endif; ?>
                <small><?= e(date('d/m/Y H:i', strtotime($p['effectue_le']))) ?> · <code><?= e($p['numero_poche']) ?></code></small>
              <?php elseif (($m['decision'] ?? '') === 'AJOURNE'): ?>
                <span class="badge-inline badge-danger">Ajourné à l’examen</span>
              <?php elseif ($p['statut'] === 'ARRIVEE'): ?>
                <span class="badge-inline badge-neutral">En cours d’examen</span>
              <?php else: ?>
                <span class="muted">Attendu</span>
              <?php endif; ?>
            </td>
            <td class="table-actions">
              <?php if ($p['statut'] === 'ARRIVEE'): ?>
                <button type="button" class="button button-outline button-small" data-open-modal="modal-medical-<?= (int)$p['id'] ?>">Dossier médical</button>
              <?php endif; ?>
              <?php if (!$p['effectue_le']): ?>
                <form method="post" action="index.php?page=participation-retirer" data-confirm="Retirer cette personne de la collecte ?">
                  <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                  <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
                  <input type="hidden" name="participation_id" value="<?= (int)$p['id'] ?>">
                  <button class="button button-danger button-small">Retirer</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- ONGLET 2 : PARCOURS MÉDICAL, PRÉLÈVEMENTS & LABO -->
<section id="tab-medical" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel">
    <div class="panel-heading">
      <div>
        <h2>Suivi médical des donneurs arrivés</h2>
        <p class="muted">Étape 1 : Examen & aptitude · Étape 2 : Volume & heure du don · Étape 3 : Confirmation du groupe sanguin et absence d’infection au laboratoire.</p>
      </div>
      <span class="pill"><?= $nbArrivees ?> donneur(s) arrivé(s)</span>
    </div>
    <?php if ($nbArrivees === 0): ?>
      <div class="empty-state">Aucun donneur n’est encore marqué comme « Arrivé » dans l’onglet Accueil.</div>
    <?php else: ?>
      <div class="clinical-list">
        <?php foreach ($participations as $p):
          if ($p['statut'] !== 'ARRIVEE') continue;
          $m = $medicalMap[$p['id']] ?? [];
          $decision = $m['decision'] ?? 'EN_ATTENTE';
          $estSainCard = (($m['resultat_statut'] ?? '') === 'VALIDE')
              && !empty($m['groupe_confirme'])
              && ($m['vih'] ?? '') === 'NON_REACTIF'
              && ($m['vhb'] ?? '') === 'NON_REACTIF'
              && ($m['vhc'] ?? '') === 'NON_REACTIF'
              && ($m['syphilis'] ?? '') === 'NON_REACTIF';
        ?>
        <article class="clinical-row-card">
          <div class="clinical-row-info">
            <div class="clinical-row-title">
              <strong>#<?= (int)$p['donneur_id'] ?> · <?= e($p['nom']) ?></strong>
              <?php if ($p['effectue_le']): ?>
                <span class="badge-inline badge-ok">✓ A donné ici (<?= (int)$p['volume_ml'] ?> ml · <?= e($p['numero_poche']) ?>)</span>
                <?php if ($estSainCard): ?>
                  <span class="blood-chip blood-chip-sain"><strong>✓ Groupe <?= e($m['groupe_confirme']) ?> confirmé sain</strong></span>
                <?php else: ?>
                  <span class="badge-inline badge-warn">⏳ Groupe & dépistages en attente labo</span>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($p['en_delai_12_semaines']): ?>
                <span class="badge-inline badge-warn">⏳ Délai 12 sem. jusqu’au <?= e(date('d/m/Y', strtotime((string)$p['prochain_don_possible_le']))) ?></span>
              <?php endif; ?>
            </div>
            <p class="muted">
              Arrivée : <strong><?= $p['arrivee_le'] ? e(date('d/m/Y à H:i', strtotime($p['arrivee_le']))) : '—' ?></strong>
              <?php if (!empty($m['tension_systolique'])): ?>
                · Constantes : <strong><?= (int)$m['tension_systolique'] ?>/<?= (int)$m['tension_diastolique'] ?> mmHg</strong>, Pouls <?= (int)$m['pouls'] ?>/min, <?= e((string)$m['temperature_c']) ?> °C, Hb <?= e((string)$m['hemoglobine_g_dl']) ?> g/dL, <?= e((string)$m['poids_kg']) ?> kg
              <?php endif; ?>
              <?php if ($p['effectue_le']): ?>
                · Prélèvement : <strong><?= e(date('d/m/Y à H:i', strtotime($p['effectue_le']))) ?></strong>
              <?php endif; ?>
            </p>
          </div>
          <div class="clinical-row-steps">
            <button type="button" class="step-btn <?= $decision==='APTE'?'step-done':($decision==='AJOURNE'?'step-warn':'') ?>" data-open-modal="modal-medical-<?= (int)$p['id'] ?>">
              <small>1. Examen avant don</small>
              <strong><?= $decision==='APTE'?'✓ Apte':($decision==='AJOURNE'?'✕ Ajourné':'À compléter') ?></strong>
            </button>
            <button type="button" class="step-btn <?= $p['effectue_le']?'step-done':'' ?>" data-open-modal="modal-don-<?= (int)$p['id'] ?>" <?= $decision!=='APTE' && !$p['effectue_le'] ? 'disabled title="Validez d’abord l’examen en Apte"' : '' ?>>
              <small>2. Prélèvement & Heure</small>
              <strong><?= $p['effectue_le'] ? (int)$p['volume_ml'] . ' ml prélevés' : 'Confirmer volume' ?></strong>
            </button>
            <button type="button" class="step-btn <?= $estSainCard?'step-done':(($m['resultat_statut']??'')==='CONTACT_CENTRE'?'step-warn':'') ?>" data-open-modal="modal-labo-<?= (int)$p['id'] ?>" <?= !$p['effectue_le'] ? 'disabled title="Nécessite un don confirmé"' : '' ?>>
              <small>3. Labo (Groupe & Sain)</small>
              <strong><?= $estSainCard ? '✓ ' . e($m['groupe_confirme']) . ' (Sain)' : (($m['resultat_statut']??'')==='CONTACT_CENTRE'?'Écarté / Contact':'À confirmer') ?></strong>
            </button>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ONGLET 3 : CRÉNEAUX & CAPACITÉS -->
<section id="tab-creneaux" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel">
    <div class="panel-heading">
      <div>
        <h2>Créneaux horaires et places disponibles</h2>
        <p class="muted">Les horaires d’ajout sont pré-remplis automatiquement à la suite du dernier créneau, tout en restant entièrement modifiables.</p>
      </div>
      <span><?= count($slots) ?> créneau(x)</span>
    </div>
    <div class="table-wrap">
      <table class="data-table schedule-table">
        <thead><tr><th>Date</th><th>Horaire</th><th>Capacité</th><th>Réservées</th><th>Disponibles</th><th>Occupation</th><th>État</th></tr></thead>
        <tbody>
          <?php foreach ($slots as $s):
            $cap = max(1, (int)$s['capacite']);
            $prises = (int)$s['places_prises'];
            $libres = max(0, $cap - $prises);
            $pctSlot = min(100, round(($prises / $cap) * 100));
          ?>
          <tr>
            <td data-label="Date"><?= e(date('d/m/Y',strtotime($s['debut']))) ?></td>
            <td data-label="Horaire"><strong><?= e(date('H:i',strtotime($s['debut']))) ?> – <?= e(date('H:i',strtotime($s['fin']))) ?></strong></td>
            <td data-label="Capacité"><?= (int)$s['capacite'] ?> place(s)</td>
            <td data-label="Réservées"><?= $prises ?></td>
            <td data-label="Disponibles"><strong><?= $libres ?></strong></td>
            <td data-label="Occupation"><div class="mini-progress"><div style="width:<?= $pctSlot ?>%"></div></div></td>
            <td data-label="État"><span class="pill <?= $s['statut']==='OUVERT'?'':'closed' ?>"><?= e($s['statut']==='OUVERT'?'Ouvert':'Fermé') ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <form method="post" action="index.php?page=creneau-sauver" data-validate="creneau" class="inline-fields">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <div class="field"><label for="debut">Début du créneau (modifiable)</label><input id="debut" name="debut" type="datetime-local" value="<?= e($nextSlotStartInput) ?>"></div>
      <div class="field"><label for="fin">Fin du créneau (modifiable)</label><input id="fin" name="fin" type="datetime-local" value="<?= e($nextSlotEndInput) ?>"></div>
      <div class="field"><label for="capacite">Nombre de places</label><input id="capacite" name="capacite" type="number" min="1" max="100" value="<?= (int)($settings['capacite_defaut']??2) ?>"></div>
      <button class="button button-primary">+ Ajouter ce créneau</button>
    </form>
  </div>
</section>

<!-- FENÊTRE MODALE : INVITER UN DONNEUR SANS COMPTE -->
<div class="modal-backdrop" id="modal-invite-walkin" hidden>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="title-invite-walkin">
    <header class="modal-header">
      <div>
        <span class="eyebrow">ACCUEIL SUR PLACE</span>
        <h3 id="title-invite-walkin">Créer un compte pour un donneur sans compte</h3>
      </div>
      <button type="button" class="modal-close" data-close-modal aria-label="Fermer">✕</button>
    </header>
    <p class="muted">Après accord du donneur, créez son invitation personnelle (valable 30 jours). Son numéro de compte s’affichera aussitôt pour enregistrer son arrivée.</p>
    <form method="post" action="index.php?page=inviter-donneur" data-validate="invitation" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <div class="form-grid">
        <?php foreach (['nom'=>'Nom complet','email'=>'Adresse e-mail','telephone'=>'Téléphone','ville'=>'Ville'] as $key=>$label): ?>
        <div class="field"><label for="invite-<?= e($key) ?>"><?= e($label) ?></label><input id="invite-<?= e($key) ?>" name="<?= e($key) ?>" <?= $key==='email'?'type="email"':'' ?>></div>
        <?php endforeach; ?>
        <div class="field full"><label for="invite-date">Date de naissance (18 ans minimum)</label><input id="invite-date" name="date_naissance" type="date"></div>
      </div>
      <label class="checkbox-line"><input type="checkbox" name="consentement" value="1"> Accord du donneur obtenu pour la création de son espace</label>
      <div class="modal-footer">
        <button type="button" class="button button-quiet" data-close-modal>Annuler</button>
        <button class="button button-primary">Créer l’invitation</button>
      </div>
    </form>
  </div>
</div>

<!-- FENÊTRES MODALES PAR PARTICIPANT (HEURE D'ARRIVÉE, EXAMEN, DON, LABO) -->
<?php foreach ($participations as $p):
  $m = $medicalMap[$p['id']] ?? [];
  $pid = (int)$p['id'];
  $arriveeInput = $p['arrivee_le'] ? date('Y-m-d\TH:i', strtotime($p['arrivee_le'])) : $nowLocal;
  $examenInput = !empty($m['examen_saisi_le']) ? date('Y-m-d\TH:i', strtotime($m['examen_saisi_le'])) : $arriveeInput;
  $donInput = $p['effectue_le'] ? date('Y-m-d\TH:i', strtotime($p['effectue_le'])) : $arriveeInput;
  $defaultNextDate = $p['effectue_le']
      ? date('Y-m-d', strtotime($p['effectue_le'] . ' +12 weeks'))
      : date('Y-m-d', strtotime('+12 weeks'));
  $prochainDonInput = !empty($m['prochain_don_le']) ? (string)$m['prochain_don_le'] : $defaultNextDate;
  $groupePreselection = ($m['groupe_confirme'] ?? null) ?: ($p['groupe_sanguin'] ?? '');
?>
<!-- Modale Modification Heure d'Arrivée -->
<div class="modal-backdrop" id="modal-arrivee-<?= $pid ?>" hidden>
  <div class="modal-card modal-card-sm" role="dialog" aria-modal="true">
    <header class="modal-header">
      <div><span class="eyebrow">HORODATAGE D’ACCUEIL</span><h3>Modifier l’heure d’arrivée — <?= e($p['nom']) ?></h3></div>
      <button type="button" class="modal-close" data-close-modal aria-label="Fermer">✕</button>
    </header>
    <form method="post" action="index.php?page=arriver" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <input type="hidden" name="participation_id" value="<?= $pid ?>">
      <div class="field">
        <label for="arr-time-<?= $pid ?>">Date et heure d’arrivée enregistrées</label>
        <input id="arr-time-<?= $pid ?>" type="datetime-local" name="arrivee_le" value="<?= e($arriveeInput) ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="button button-quiet" data-close-modal>Annuler</button>
        <button class="button button-primary">Enregistrer l’horaire</button>
      </div>
    </form>
  </div>
</div>

<?php if ($p['statut'] === 'ARRIVEE'): ?>
<!-- Modale 1 : Examen avant don -->
<div class="modal-backdrop" id="modal-medical-<?= $pid ?>" hidden>
  <div class="modal-card" role="dialog" aria-modal="true">
    <header class="modal-header">
      <div><span class="eyebrow">ÉTAPE 1 · EXAMEN PRÉ-DON</span><h3><?= e($p['nom']) ?> (Code <?= e($p['code_confirmation']) ?>)</h3></div>
      <button type="button" class="modal-close" data-close-modal aria-label="Fermer">✕</button>
    </header>
    <form method="post" action="index.php?page=examen-sauver" data-validate="examen" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <input type="hidden" name="participation_id" value="<?= $pid ?>">
      <div class="exam-grid">
        <?php foreach (['tension_systolique'=>['Tension systolique','mmHg','1'], 'tension_diastolique'=>['Tension diastolique','mmHg','1'],'pouls'=>['Pouls','/min','1'],'temperature_c'=>['Température','°C','0.1'],'hemoglobine_g_dl'=>['Hémoglobine','g/dL','0.1'],'poids_kg'=>['Poids','kg','0.1']] as $key=>[$label,$unit,$step]): ?>
        <div class="field">
          <label for="<?= e($key) ?>-<?= $pid ?>"><?= e($label) ?> (<?= e($unit) ?>)</label>
          <input id="<?= e($key) ?>-<?= $pid ?>" name="<?= e($key) ?>" type="number" step="<?= e($step) ?>" value="<?= e((string)($m[$key]??'')) ?>">
        </div>
        <?php endforeach; ?>
      </div>
      <div class="form-grid">
        <div class="field">
          <label for="decision-<?= $pid ?>">Décision du professionnel</label>
          <select id="decision-<?= $pid ?>" name="decision">
            <?php foreach (['EN_ATTENTE'=>'En attente','APTE'=>'Apte au prélèvement (comptabilise le don)','AJOURNE'=>'Ajourné'] as $value=>$label): ?>
              <option value="<?= e($value) ?>" <?= ($m['decision']??'EN_ATTENTE')===$value?'selected':'' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="saisi-le-<?= $pid ?>">Date et heure de l’examen (modifiable)</label>
          <input id="saisi-le-<?= $pid ?>" type="datetime-local" name="saisi_le" value="<?= e($examenInput) ?>">
        </div>
      </div>
      <div class="field">
        <label for="exam-vol-<?= $pid ?>">Volume de la poche si déclaré Apte (420 à 480 ml)</label>
        <input id="exam-vol-<?= $pid ?>" name="volume_ml" type="number" min="420" max="480" value="<?= (int)($p['volume_ml'] ?: 450) ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="button button-quiet" data-close-modal>Fermer</button>
        <button class="button button-primary">Enregistrer l’examen</button>
      </div>
    </form>
  </div>
</div>

<!-- Modale 2 : Prélèvement & Horaire du don -->
<div class="modal-backdrop" id="modal-don-<?= $pid ?>" hidden>
  <div class="modal-card modal-card-sm" role="dialog" aria-modal="true">
    <header class="modal-header">
      <div><span class="eyebrow">ÉTAPE 2 · PRÉLÈVEMENT SANGUIN</span><h3>Volume et heure du don — <?= e($p['nom']) ?></h3></div>
      <button type="button" class="modal-close" data-close-modal aria-label="Fermer">✕</button>
    </header>
    <form method="post" action="index.php?page=confirmer-don" data-validate="don" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <input type="hidden" name="participation_id" value="<?= $pid ?>">
      <div class="field">
        <label for="don-vol-<?= $pid ?>">Volume réellement prélevé (420 à 480 ml)</label>
        <input id="don-vol-<?= $pid ?>" name="volume_ml" type="number" min="420" max="480" value="<?= (int)($p['volume_ml'] ?: 450) ?>">
      </div>
      <div class="field">
        <label for="don-time-<?= $pid ?>">Date et heure du prélèvement (pré-remplie, modifiable)</label>
        <input id="don-time-<?= $pid ?>" name="effectue_le" type="datetime-local" value="<?= e($donInput) ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="button button-quiet" data-close-modal>Fermer</button>
        <button class="button button-primary">Enregistrer le prélèvement</button>
      </div>
    </form>
  </div>
</div>

<!-- Modale 3 : Résultat Laboratoire & Prochain don (12 semaines) -->
<div class="modal-backdrop" id="modal-labo-<?= $pid ?>" hidden>
  <div class="modal-card" role="dialog" aria-modal="true">
    <header class="modal-header">
      <div><span class="eyebrow">ÉTAPE 3 · QUALIFICATION BIOLOGIQUE DE LA POCHE</span><h3>Groupe sanguin & dépistages — <?= e($p['nom']) ?></h3></div>
      <button type="button" class="modal-close" data-close-modal aria-label="Fermer">✕</button>
    </header>
    <p class="muted">Lorsque le groupe est renseigné, que les 4 dépistages sont « Non réactif » (aucune infection) et que le statut est « Validé », la poche (<?= (int)($p['volume_ml'] ?: 450) ?> ml) passe de « Groupe inconnu » à ce groupe sanguin confirmé dans les statistiques.</p>
    <form method="post" action="index.php?page=resultat-sauver" data-validate="resultat" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <input type="hidden" name="collecte_id" value="<?= (int)$detail['id'] ?>">
      <input type="hidden" name="participation_id" value="<?= $pid ?>">
      <div class="form-grid">
        <div class="field">
          <label>Groupe sanguin confirmé par le labo</label>
          <select name="groupe_confirme">
            <option value="">Non déterminé / En attente</option>
            <?php foreach (SosController::GROUPES as $g): ?>
              <option value="<?= e($g) ?>" <?= $groupePreselection===$g?'selected':'' ?>><?= e($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Qualification de la poche</label>
          <select name="statut">
            <?php foreach (['EN_ATTENTE'=>'En attente d’analyse','VALIDE'=>'Validé (Sang sain & groupe confirmé)','CONTACT_CENTRE'=>'Écarté / Contacter le centre'] as $val=>$lib): ?>
              <option value="<?= e($val) ?>" <?= ($m['resultat_statut']??'EN_ATTENTE')===$val?'selected':'' ?>><?= e($lib) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php foreach (['vih'=>'Dépistage VIH','vhb'=>'Hépatite B (VHB)','vhc'=>'Hépatite C (VHC)','syphilis'=>'Syphilis'] as $key=>$label): ?>
        <div class="field">
          <label><?= e($label) ?></label>
          <select name="<?= e($key) ?>">
            <?php foreach (['EN_ATTENTE'=>'En attente','NON_REACTIF'=>'Non réactif (Sain)','A_CONFIRMER'=>'À confirmer'] as $val=>$lib): ?>
              <option value="<?= e($val) ?>" <?= ($m[$key]??'EN_ATTENTE')===$val?'selected':'' ?>><?= e($lib) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endforeach; ?>
        <div class="field">
          <label>Avis médical pour un prochain don</label>
          <select name="statut_prochain_don">
            <?php foreach (['EN_COURS'=>'En cours d’évaluation','AUTORISE'=>'Autorisé à partir de la date indiquée','NON_AUTORISE'=>'Non autorisé'] as $val=>$lib): ?>
              <option value="<?= e($val) ?>" <?= ($m['statut_prochain_don']??'EN_COURS')===$val?'selected':'' ?>><?= e($lib) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Prochaine date autorisée (auto +12 sem., modifiable)</label>
          <input type="date" name="prochain_don_le" value="<?= e($prochainDonInput) ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="button button-quiet" data-close-modal>Fermer</button>
        <button class="button button-primary">Enregistrer le résultat</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
<?php endforeach; ?>
