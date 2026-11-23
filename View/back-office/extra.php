<?php if ($page==='tableau-bord'):
  $volTotal = (int)($stats['volume_ml'] ?? 0);
  $volValide = (int)($stats['volume_valide_ml'] ?? 0);
  $volInconnu = (int)($stats['volume_inconnu_ml'] ?? 0);
  $donsTotal = (int)($stats['dons_confirmes'] ?? 0);
  $donneursTotal = max(1, (int)($stats['donneurs'] ?? 0));
  $eligibles = (int)($stats['donneurs_eligibles'] ?? 0);
  $enDelai = (int)($stats['donneurs_delai_12s'] ?? 0);
  $nonAutorises = (int)($stats['donneurs_non_autorises'] ?? 0);
  $parGroupe = $stats['par_groupe'] ?? [];
  $maxGroupeVol = 1;
  foreach ($parGroupe as $gRow) {
      if ((int)$gRow['volume_ml'] > $maxGroupeVol) $maxGroupeVol = (int)$gRow['volume_ml'];
  }
?>
<div class="admin-analytics">
  <div class="kpi-banner">
    <article class="kpi-card kpi-blood">
      <div class="kpi-head"><span>SANG PRÉLEVÉ & VÉRIFIÉ</span><span class="kpi-badge"><?= number_format($volTotal / 1000, 2, ',', ' ') ?> L</span></div>
      <strong class="kpi-number"><?= number_format($volTotal, 0, ',', ' ') ?> <small>ml au total</small></strong>
      <p class="kpi-meta"><strong class="text-green"><?= number_format($volValide, 0, ',', ' ') ?> ml</strong> sains confirmés (<?= (int)($stats['poches_validees'] ?? 0) ?>) · <strong class="text-amber"><?= number_format($volInconnu, 0, ',', ' ') ?> ml</strong> groupe en attente labo</p>
    </article>
    <article class="kpi-card kpi-donors">
      <div class="kpi-head"><span>DISPONIBILITÉ DONNEURS</span><span class="kpi-badge kpi-badge-green"><?= $eligibles ?> disponible(s)</span></div>
      <strong class="kpi-number"><?= (int)($stats['donneurs'] ?? 0) ?> <small>donneurs actifs</small></strong>
      <p class="kpi-meta"><strong><?= $enDelai ?></strong> en délai de 12 semaines · <strong><?= $nonAutorises ?></strong> non autorisé(s)</p>
    </article>
    <article class="kpi-card kpi-drives">
      <div class="kpi-head"><span>COLLECTES & ÉQUIPES</span><span class="kpi-badge kpi-badge-blue"><?= (int)($stats['collectes_publiees'] ?? 0) ?> publiée(s)</span></div>
      <strong class="kpi-number"><?= (int)($stats['collectes'] ?? 0) ?> <small>collecte(s)</small></strong>
      <p class="kpi-meta"><?= (int)($stats['arrivees'] ?? 0) ?> arrivée(s) · <?= (int)($stats['examens_aptes'] ?? 0) ?> apte(s) · <?= (int)($stats['responsables'] ?? 0) ?> responsable(s)</p>
    </article>
    <article class="kpi-card kpi-sos">
      <div class="kpi-head"><span>URGENCES SOS & TRAFIC</span><span class="kpi-badge"><?= (int)($stats['sos_actives'] ?? 0) ?> SOS actif(s)</span></div>
      <strong class="kpi-number"><?= (int)($stats['reservations'] ?? 0) + (int)($stats['visites_sur_place'] ?? 0) ?> <small>participations</small></strong>
      <p class="kpi-meta"><?= (int)($stats['reservations'] ?? 0) ?> en ligne · <?= (int)($stats['visites_sur_place'] ?? 0) ?> sur place · <?= number_format((int)($stats['pages_vues'] ?? 0), 0, ',', ' ') ?> vues</p>
    </article>
  </div>

  <div class="analytics-grid">
    <section class="panel chart-panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">STOCK SANGUIN CONFIRMÉ SAIN VS EN ATTENTE</span>
          <h2>Répartition du sang par groupe confirmé au laboratoire</h2>
        </div>
        <span class="pill"><?= number_format($volTotal, 0, ',', ' ') ?> ml prélevés</span>
      </div>
      <p class="muted">Une poche n’obtient son groupe sanguin définitif qu’après validation du laboratoire confirmant l’absence d’infection (4 dépistages non réactifs). Avant cela, elle figure en « En attente labo ».</p>
      <div class="blood-chart">
        <?php foreach ($parGroupe as $gr):
          $isPending = ($gr['groupe'] === 'En attente labo');
          $pctWidth = $volTotal > 0 ? round(((int)$gr['volume_ml'] / $maxGroupeVol) * 100) : 0;
          $pctShare = $volTotal > 0 ? round(((int)$gr['volume_ml'] / $volTotal) * 100) : 0;
        ?>
        <div class="blood-bar-row <?= $isPending ? 'blood-bar-pending' : '' ?>">
          <div class="blood-group-tag <?= $isPending ? 'tag-pending' : '' ?>"><?= $isPending ? 'Inconnu / Labo' : e($gr['groupe']) . ' ✓' ?></div>
          <div class="blood-bar-track">
            <div class="blood-bar-fill <?= $isPending ? 'fill-pending' : '' ?>" style="width:<?= max($gr['volume_ml'] > 0 ? 8 : 0, $pctWidth) ?>%"></div>
          </div>
          <div class="blood-bar-stats">
            <strong><?= number_format((int)$gr['volume_ml'], 0, ',', ' ') ?> ml</strong>
            <small><?= (int)$gr['poches'] ?> poche(s) (<?= $pctShare ?>%) · <?= $isPending ? 'Groupe à confirmer' : 'Sain & vérifié' ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel chart-panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">PARCOURS CLINIQUE & RÈGLE DES 12 SEMAINES</span>
          <h2>Entonnoir des dons & état des donneurs</h2>
        </div>
      </div>
      <?php
        $totalPart = max(1, (int)($stats['reservations'] ?? 0) + (int)($stats['visites_sur_place'] ?? 0));
        $funnelSteps = [
            ['Inscriptions & venues (En ligne + Sur place)', (int)($stats['reservations'] ?? 0) + (int)($stats['visites_sur_place'] ?? 0), 100, '#3268dc'],
            ['Arrivées enregistrées au centre', (int)($stats['arrivees'] ?? 0), min(100, round(((int)($stats['arrivees'] ?? 0) / $totalPart) * 100)), '#0ea5e9'],
            ['Donneurs vérifiés Aptes (sang prélevé)', (int)($stats['examens_aptes'] ?? 0), min(100, round(((int)($stats['examens_aptes'] ?? 0) / $totalPart) * 100)), '#d92735'],
            ['Poches confirmées saines + groupe validé au labo', (int)($stats['poches_validees'] ?? 0), min(100, round(((int)($stats['poches_validees'] ?? 0) / $totalPart) * 100)), '#15966a'],
        ];
      ?>
      <div class="funnel-list">
        <?php foreach ($funnelSteps as [$fLabel, $fVal, $fPct, $fColor]): ?>
        <div class="funnel-item">
          <div class="funnel-meta"><span><?= e($fLabel) ?></span><strong><?= $fVal ?> <small>(<?= $fPct ?>%)</small></strong></div>
          <div class="funnel-track"><div class="funnel-fill" style="width:<?= max($fVal > 0 ? 6 : 0, $fPct) ?>%;background:<?= e($fColor) ?>"></div></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="eligibility-cards-box">
        <span class="eyebrow">SUIVI DU DÉLAI LÉGAL DE 12 SEMAINES</span>
        <div class="eligibility-tiles">
          <div class="elig-tile elig-tile-ok">
            <strong><?= $eligibles ?></strong>
            <span>Éligibles au don</span>
            <small>Peuvent réserver aujourd’hui</small>
          </div>
          <div class="elig-tile elig-tile-warn">
            <strong><?= $enDelai ?></strong>
            <span>En délai de 12 sem.</span>
            <small>Récupération post-don active</small>
          </div>
          <div class="elig-tile elig-tile-danger">
            <strong><?= $nonAutorises ?></strong>
            <span>Non autorisés</span>
            <small>Avis médical du centre</small>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="panel">
    <div class="panel-heading">
      <div>
        <span class="eyebrow">BILAN PAR OPÉRATION (CLIQUEZ SUR UNE COLLECTE POUR VOIR SES DONNEURS & POCHES)</span>
        <h2>Statistiques détaillées de chaque collecte</h2>
      </div>
      <div class="search-box">
        <input type="search" data-table-search="#admin-collectes-table" placeholder="Rechercher une collecte (titre, ville, centre)…">
      </div>
    </div>
    <div class="table-wrap">
      <table class="data-table" id="admin-collectes-table">
        <thead>
          <tr>
            <th>Collecte & Centre</th>
            <th>Date & Statut</th>
            <th>Participations</th>
            <th>Sang prélevé (Total / Sain / Inconnu)</th>
            <th>Répartition par groupe sanguin dans cette collecte</th>
            <th>Détails & Donneurs</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($stats['par_collecte'] ?? []) as $col):
            $cid = (int)$col['id'];
          ?>
          <tr data-search-row class="clickable-collecte-row">
            <td>
              <strong><?= e($col['titre']) ?></strong>
              <small><?= e($col['ville']) ?> · <?= e($col['nom_organisation']) ?> (<?= e($col['organisateur']) ?>)</small>
            </td>
            <td>
              <strong><?= e(date('d/m/Y H:i', strtotime($col['debut']))) ?></strong>
              <span class="pill <?= $col['statut']==='PUBLIEE'?'':($col['statut']==='BROUILLON'?'draft':'closed') ?>"><?= e($col['statut']) ?></span>
            </td>
            <td>
              <strong><?= (int)$col['nb_arrivees'] ?> arrivé(s)</strong> · <span class="badge-inline badge-ok"><?= (int)$col['nb_aptes'] ?> Apte(s)</span>
              <small><?= (int)$col['nb_reservations'] ?> rés. · <?= (int)$col['nb_sur_place'] ?> sur place · <?= (int)$col['nb_creneaux'] ?> créneau(x)</small>
            </td>
            <td>
              <strong class="blood-volume-highlight"><?= number_format((int)$col['volume_ml'], 0, ',', ' ') ?> ml au total</strong>
              <small><span class="text-green"><?= number_format((int)$col['volume_valide_ml'], 0, ',', ' ') ?> ml sains</span> · <span class="text-amber"><?= number_format((int)$col['volume_inconnu_ml'], 0, ',', ' ') ?> ml en attente</span></small>
            </td>
            <td>
              <?php if (!empty($col['groupes'])): ?>
                <div class="chip-list">
                  <?php foreach ($col['groupes'] as $cg): ?>
                    <?php if ($cg['etat'] === 'SAIN_CONFIRME'): ?>
                      <span class="blood-chip blood-chip-sain"><strong>✓ <?= e($cg['groupe']) ?> (Sain)</strong> · <?= (int)$cg['volume_ml'] ?> ml (<?= (int)$cg['poches'] ?>)</span>
                    <?php elseif ($cg['etat'] === 'ECARTE'): ?>
                      <span class="badge-inline badge-danger">✕ Écarté · <?= (int)$cg['volume_ml'] ?> ml</span>
                    <?php else: ?>
                      <span class="badge-inline badge-warn">⏳ Groupe inconnu / Labo en attente · <?= (int)$cg['volume_ml'] ?> ml (<?= (int)$cg['poches'] ?>)</span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <span class="muted">Aucun prélèvement</span>
              <?php endif; ?>
            </td>
            <td>
              <button type="button" class="button button-outline button-small" data-open-modal="modal-collecte-detail-<?= $cid ?>">
                Voir donneurs & délais (<?= count($col['participants_details'] ?? []) ?>)
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Fenêtres modales : Donneurs, Poches et Délais de 12 semaines de chaque collecte -->
  <?php foreach (($stats['par_collecte'] ?? []) as $col):
    $cid = (int)$col['id'];
    $pList = $col['participants_details'] ?? [];
  ?>
  <div class="modal-backdrop" id="modal-collecte-detail-<?= $cid ?>" hidden>
    <div class="modal-card modal-card-lg" role="dialog" aria-modal="true">
      <header class="modal-header">
        <div>
          <span class="eyebrow">DÉTAIL DE LA COLLECTE · <?= e($col['ville']) ?> (<?= e(date('d/m/Y', strtotime($col['debut']))) ?>)</span>
          <h3><?= e($col['titre']) ?> — <?= number_format((int)$col['volume_ml'], 0, ',', ' ') ?> ml collectés</h3>
          <p class="muted"><?= number_format((int)$col['volume_valide_ml'], 0, ',', ' ') ?> ml confirmés sains au labo · <?= number_format((int)$col['volume_inconnu_ml'], 0, ',', ' ') ?> ml dont le groupe est en attente de validation labo.</p>
        </div>
        <button type="button" class="modal-close" data-close-modal aria-label="Fermer">✕</button>
      </header>
      <?php if (empty($pList)): ?>
        <div class="empty-state">Aucun participant enregistré sur cette collecte.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Donneur</th>
                <th>Parcours</th>
                <th>Poche & Date du don</th>
                <th>Volume</th>
                <th>Groupe confirmé & État sain</th>
                <th>Délai de 12 semaines du donneur</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pList as $part): ?>
              <tr>
                <td>
                  <strong>#<?= (int)$part['donneur_id'] ?> · <?= e($part['nom']) ?></strong>
                  <small><?= e($part['email']) ?></small>
                </td>
                <td>
                  <span class="pill <?= $part['mode']==='SUR_PLACE'?'draft':'' ?>"><?= $part['mode']==='SUR_PLACE'?'Sur place':'En ligne' ?></span>
                  <small><?= e($part['statut']) ?> · Examen : <?= e($part['examen_decision']) ?></small>
                </td>
                <td>
                  <?php if ($part['a_donne']): ?>
                    <code><?= e($part['numero_poche']) ?></code>
                    <small><?= e(date('d/m/Y à H:i', strtotime((string)$part['effectue_le']))) ?></small>
                  <?php else: ?>
                    <span class="muted">Pas de prélèvement</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($part['a_donne']): ?>
                    <strong class="blood-volume-highlight"><?= (int)$part['volume_ml'] ?> ml</strong>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!$part['a_donne']): ?>
                    <span class="muted">—</span>
                  <?php elseif ($part['est_sain']): ?>
                    <span class="blood-chip blood-chip-sain"><strong>✓ <?= e($part['groupe_confirme']) ?> (Sain)</strong></span>
                  <?php else: ?>
                    <span class="badge-inline badge-warn">⏳ Groupe inconnu / Labo en attente</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($part['delai_etat'] === 'DELAI_12_SEMAINES'): ?>
                    <span class="badge-inline badge-warn">⏳ En délai 12 sem. (<?= (int)$part['jours_restants'] ?> j restants)</span>
                    <small>Prochain don dès le <?= e(date('d/m/Y', strtotime((string)$part['prochain_don_possible_le']))) ?></small>
                  <?php elseif ($part['delai_etat'] === 'NON_AUTORISE'): ?>
                    <span class="badge-inline badge-danger">Non autorisé par le centre</span>
                  <?php else: ?>
                    <span class="badge-inline badge-ok">✓ Éligible</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <div class="modal-footer">
        <button type="button" class="button button-primary" data-close-modal>Fermer</button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php elseif ($page==='inviter-responsable'): ?>
<div class="panel"><h2>Créer l’accès d’un responsable de collecte</h2><p>Générez un lien personnel d’activation pour un nouveau responsable de centre. Il choisira lui-même son mot de passe (valable 30 jours, usage unique).</p><?php if (!empty($_SESSION['invitation_lien'])): ?><div class="notice"><p>Responsable n° <?= (int)$_SESSION['invitation_id'] ?> · <a href="<?= e($_SESSION['invitation_lien']) ?>"><?= e($_SESSION['invitation_lien']) ?></a></p><div class="invitation-qr" data-link="<?= e($_SESSION['invitation_lien']) ?>"></div></div><?php unset($_SESSION['invitation_lien'],$_SESSION['invitation_id']); endif; ?><form method="post" action="index.php?page=inviter-responsable" data-validate="invitation-responsable" class="inline-fields"><input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><div class="field"><label for="nom">Nom complet du responsable</label><input id="nom" name="nom" placeholder="Ex. Dr. Salma Mansouri"></div><div class="field"><label for="email">Adresse e-mail professionnelle</label><input id="email" name="email" type="email" placeholder="responsable@centre-transfusion.tn"></div><button class="button button-primary">Générer le lien d’activation</button></form></div>

<?php elseif ($page==='gestion-sos'): ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Lieu</th><th>Ville</th><th>Groupe</th><th>Contact</th><th>Expiration</th><th>Action</th></tr></thead><tbody><?php foreach($items as $a): ?><tr><td><strong><?= e($a['lieu']) ?></strong><?php if (!empty($a['message'])): ?><small><?= e($a['message']) ?></small><?php endif; ?></td><td><?= e($a['ville']) ?></td><td><span class="blood-chip"><strong><?= e($a['groupe_receveur']) ?></strong></span></td><td><?= e($a['telephone']) ?></td><td><?= e(date('d/m/Y H:i',strtotime($a['expire_le']))) ?></td><td><form method="post" action="index.php?page=sos-supprimer" data-confirm="Retirer cette alerte SOS ?"><input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="button button-danger button-small">Retirer</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php if (!$items): ?><div class="empty-state">Aucune alerte SOS active en ce moment.</div><?php endif; ?>

<?php elseif ($page==='parametres'):
  $nomOrg = (string)($_POST['nom_organisation'] ?? $settings['nom_organisation'] ?? 'BloodLink');
  $capDefaut = (int)($_POST['capacite_defaut'] ?? $settings['capacite_defaut'] ?? 3);
?>
<div class="form-panel">
  <span class="eyebrow">PARAMÈTRES RAPIDES</span>
  <h2>Configuration de mon centre de collecte</h2>
  <p class="muted">Réglez simplement les deux informations essentielles utilisées lorsque vous organisez une collecte.</p>
  <form method="post" action="index.php?page=parametres-sauver" data-validate="parametres">
    <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
    <div class="form-grid">
      <div class="field full">
        <label for="nom_organisation">1. Nom de votre centre ou établissement</label>
        <input id="nom_organisation" name="nom_organisation" value="<?= e($nomOrg) ?>" placeholder="Ex. Centre National de Transfusion Sanguine — Tunis">
        <small class="field-hint">Ce nom est affiché aux donneurs sur la page publique de chacune de vos collectes.</small>
      </div>
      <div class="field full">
        <label for="capacite_defaut">2. Nombre de places par défaut pour chaque nouveau créneau horaire</label>
        <input id="capacite_defaut" name="capacite_defaut" type="number" min="1" max="100" value="<?= $capDefaut ?>">
        <small class="field-hint">Quand vous ajoutez un créneau (ex. 09:00–10:00), ce nombre de places est pré-rempli automatiquement.</small>
      </div>
    </div>
    <div class="form-actions">
      <button class="button button-primary">Enregistrer</button>
    </div>
  </form>
</div>

<?php elseif ($page==='scan-donneur'): ?>
<div class="panel">
  <h2>Vérifier l’identité et l’éligibilité d’un donneur par QR</h2>
  <p>Le code QR personnel est opaque. Scannez-le avec la caméra ou collez son code de 48 caractères pour vérifier son historique et son délai de 12 semaines.</p>
  <video id="scan-video" autoplay playsinline hidden></video>
  <p id="scan-message"></p>
  <button id="scan-start" class="button button-outline" type="button">Activer la caméra QR</button>
  <form method="get" action="index.php" data-validate="scan" class="inline-fields">
    <input type="hidden" name="page" value="scan-donneur">
    <div class="field"><label for="code">Ou saisir le code personnel du QR (48 caractères)</label><input id="code" name="code" value="<?= e($_GET['code']??'') ?>"></div>
    <button class="button button-primary">Vérifier le donneur</button>
  </form>
  <?php if (isset($_GET['code'])): ?>
    <?php if ($scan): ?>
      <div class="donor-scan-card">
        <div class="donor-scan-header">
          <div>
            <span class="eyebrow">COMPTE DONNEUR N° <?= (int)$scan['id'] ?></span>
            <h3><?= e($scan['nom']) ?></h3>
            <p><?= e($scan['email']) ?> · <?= e($scan['telephone'] ?? 'Téléphone non renseigné') ?> · <?= e($scan['ville'] ?? '') ?></p>
          </div>
          <span class="blood-chip">Groupe confirmé : <strong><?= e(($eligibilite['groupe_confirme'] ?? null) ?: 'En attente labo') ?></strong></span>
        </div>
        <?php if ($eligibilite): ?>
          <div class="donor-scan-stats">
            <div><small>Dons confirmés sur le compte</small><strong><?= (int)$eligibilite['total_dons'] ?> don(s) (<?= (int)$eligibilite['volume_total_ml'] ?> ml)</strong></div>
            <div><small>Dernier don enregistré</small><strong><?= $eligibilite['dernier_don_le'] ? e(date('d/m/Y à H:i', strtotime((string)$eligibilite['dernier_don_le']))) . ' (' . e((string)$eligibilite['dernier_don_titre']) . ')' : 'Aucun don précédent' ?></strong></div>
            <div>
              <small>Statut délai 12 semaines</small>
              <?php if ($eligibilite['statut_centre'] === 'NON_AUTORISE'): ?>
                <span class="badge-inline badge-danger">Non autorisé par le centre</span>
              <?php elseif ($eligibilite['en_delai_12_semaines']): ?>
                <span class="badge-inline badge-warn">⏳ En délai de 12 semaines — Prochain don dès le <?= e(date('d/m/Y', strtotime((string)$eligibilite['prochain_don_le']))) ?> (<?= (int)$eligibilite['jours_restants'] ?> j restants)</span>
              <?php else: ?>
                <span class="badge-inline badge-ok">✓ Éligible au don aujourd’hui</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
        <p class="muted">Utilisez le numéro de compte <strong><?= (int)$scan['id'] ?></strong> dans l’onglet « Accueil & Arrivées » de votre collecte.</p>
      </div>
    <?php else: ?>
      <p class="notice">Aucun donneur actif trouvé pour ce code QR.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
