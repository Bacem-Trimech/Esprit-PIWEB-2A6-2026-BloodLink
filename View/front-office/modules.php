<?php
/**
 * Vues Front-Office des 4 modules intégrés :
 * - Annuaire Médical (annuaire, annuaire-detail)
 * - Rendez-vous Médicaux (rendez-vous)
 * - Pharmacies & Médicaments (pharmacies)
 * - Laboratoires & Analyses (laboratoires)
 */
?>
<div class="page-wrap">
  <?php foreach ($errors as $err): ?><p class="notice"><?= e($err) ?></p><?php endforeach; ?>
  <?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?>

<?php if ($page === 'annuaire'): ?>
  <div class="page-intro">
    <div>
      <span class="eyebrow">MODULE 1 · ANNUAIRE MÉDICAL & SERVICES DE SANTÉ</span>
      <h1>Établissements de santé</h1>
      <p>Consultez les hôpitaux, cliniques, pharmacies et laboratoires reliés à leurs services, praticiens et stocks en temps réel.</p>
    </div>
    <?php if ($current && in_array($current['role'], ['ADMIN', 'RESPONSABLE_ANNUAIRE'], true)): ?>
      <a class="button button-primary" href="index.php?page=admin-annuaire">Gérer l’annuaire</a>
    <?php endif; ?>
  </div>

  <section class="filter-bar-panel">
    <form method="get" action="index.php" class="filter-bar-grid">
      <input type="hidden" name="page" value="annuaire">
      <div class="field">
        <label for="ann-q">Rechercher</label>
        <input id="ann-q" name="q" value="<?= e((string)($_GET['q'] ?? '')) ?>" placeholder="Nom, adresse, service…">
      </div>
      <div class="field">
        <label for="ann-type">Type d’établissement</label>
        <select id="ann-type" name="type">
          <option value="">Tous les types</option>
          <?php foreach (EtablissementSante::TYPES as $tKey => $tLabel): ?>
            <option value="<?= e($tKey) ?>" <?= ($_GET['type'] ?? '') === $tKey ? 'selected' : '' ?>><?= e($tLabel) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="ann-ville">Ville</label>
        <select id="ann-ville" name="ville">
          <option value="">Toutes les villes</option>
          <?php foreach ($moduleData['villes'] ?? [] as $v): ?>
            <option value="<?= e($v) ?>" <?= ($_GET['ville'] ?? '') === $v ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-bar-actions">
        <button class="button button-primary button-small">Filtrer</button>
        <a class="button button-quiet button-small" href="index.php?page=annuaire">Réinitialiser</a>
      </div>
    </form>
  </section>

  <div class="card-grid">
    <?php foreach ($moduleData['etablissements'] ?? [] as $et):
      $typeLabel = EtablissementSante::TYPES[$et['type']] ?? $et['type'];
    ?>
    <article class="panel module-card">
      <div class="card-badges">
        <span class="pill <?= $et['type'] === 'pharmacie' ? '' : ($et['type'] === 'laboratoire' ? 'draft' : 'closed') ?>"><?= e($typeLabel) ?></span>
        <span class="badge-inline badge-neutral">📍 <?= e($et['ville']) ?></span>
        <?php if (!empty($et['garde_jusquau'])): ?>
          <span class="badge-inline badge-ok">✓ De garde aujourd’hui</span>
        <?php endif; ?>
      </div>
      <h2 style="margin:12px 0 6px;font-size:1.15rem"><?= e($et['nom']) ?></h2>
      <p class="muted" style="margin:0 0 10px"><?= e($et['adresse']) ?></p>
      <?php if (!empty($et['description'])): ?>
        <p style="font-size:.86rem;color:#475569;margin:0 0 12px;line-height:1.5"><?= e($et['description']) ?></p>
      <?php endif; ?>
      <div class="chip-list" style="margin-bottom:14px">
        <?php if ((int)$et['nb_services'] > 0): ?><span class="badge-inline badge-neutral"><?= (int)$et['nb_services'] ?> service(s)</span><?php endif; ?>
        <?php if ((int)$et['nb_praticiens'] > 0): ?><span class="badge-inline badge-ok"><?= (int)$et['nb_praticiens'] ?> praticien(s)</span><?php endif; ?>
        <?php if ((int)$et['nb_analyses'] > 0): ?><span class="badge-inline badge-warn"><?= (int)$et['nb_analyses'] ?> analyse(s)</span><?php endif; ?>
      </div>
      <div class="card-foot">
        <span><?= !empty($et['telephone']) ? '📞 ' . e($et['telephone']) : 'Annuaire Santé' ?></span>
        <a class="button button-outline button-small" href="index.php?page=annuaire-detail&id=<?= (int)$et['id'] ?>">Voir la fiche & liaisons →</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if (empty($moduleData['etablissements'])): ?>
    <div class="empty-state"><h2>Aucun établissement trouvé</h2><p>Essayez d’élargir vos critères de recherche.</p></div>
  <?php endif; ?>

<?php elseif ($page === 'annuaire-detail' && !empty($detail)): ?>
  <a class="back-link" href="index.php?page=annuaire">← Retour à l’annuaire médical</a>
  <article class="panel" style="margin-top:0">
    <div class="card-badges">
      <span class="pill"><?= e(EtablissementSante::TYPES[$detail['type']] ?? $detail['type']) ?></span>
      <span class="badge-inline badge-neutral">📍 <?= e($detail['ville']) ?></span>
    </div>
    <h1 style="margin:10px 0 6px"><?= e($detail['nom']) ?></h1>
    <p class="muted" style="margin:0 0 12px"><?= e($detail['adresse']) ?><?= !empty($detail['telephone']) ? ' · 📞 ' . e($detail['telephone']) : '' ?><?= !empty($detail['email']) ? ' · ✉ ' . e($detail['email']) : '' ?></p>
    <?php if (!empty($detail['description'])): ?><p style="margin:0;line-height:1.6"><?= e($detail['description']) ?></p><?php endif; ?>
  </article>

  <div class="analytics-grid" style="margin-top:18px">
    <section class="panel chart-panel">
      <div class="panel-heading"><h2>Services médicaux internes</h2><span class="pill"><?= count($detail['services'] ?? []) ?></span></div>
      <?php if (!empty($detail['services'])): ?>
        <div class="clinical-list">
          <?php foreach ($detail['services'] as $srv): ?>
            <div class="slot-row">
              <div>
                <strong><?= e($srv['nom']) ?></strong>
                <small><?= e($srv['specialite']) ?><?= !empty($srv['horaires']) ? ' · ' . e($srv['horaires']) : '' ?><?= !empty($srv['telephone']) ? ' · 📞 ' . e($srv['telephone']) : '' ?></small>
              </div>
              <?php if ((int)$srv['est_urgence']): ?><span class="badge-inline badge-danger">Urgences 24/7</span><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="muted">Aucun service interne renseigné pour cet établissement.</p>
      <?php endif; ?>
    </section>

    <section class="panel chart-panel">
      <div class="panel-heading"><h2>Praticiens & Rendez-vous</h2><span class="pill"><?= count($detail['praticiens'] ?? []) ?></span></div>
      <?php if (!empty($detail['praticiens'])): ?>
        <?php foreach ($detail['praticiens'] as $pr): ?>
          <div class="slot-row">
            <div>
              <strong>Dr <?= e($pr['prenom'] . ' ' . $pr['nom']) ?></strong>
              <small><?= e($pr['specialite']) ?><?= !empty($pr['telephone']) ? ' · 📞 ' . e($pr['telephone']) : '' ?></small>
            </div>
            <a class="button button-primary button-small" href="index.php?page=rendez-vous&praticien=<?= (int)$pr['id_praticien'] ?>">Prendre RDV →</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="muted">Aucun praticien rattaché directement à cet établissement.</p>
      <?php endif; ?>
    </section>
  </div>

  <?php if (!empty($detail['stock']) || !empty($detail['gardes'])): ?>
  <section class="panel">
    <div class="panel-heading"><h2>Pharmacie : Gardes & Médicaments disponibles</h2><a class="text-link" href="index.php?page=pharmacies">Rechercher un médicament →</a></div>
    <?php if (!empty($detail['gardes'])): ?>
      <div class="chip-list" style="margin-bottom:14px">
        <?php foreach ($detail['gardes'] as $g): ?>
          <span class="badge-inline badge-ok">Garde : <?= e(date('d/m/Y', strtotime($g['start_date']))) ?> → <?= e(date('d/m/Y', strtotime($g['end_date']))) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($detail['stock'])): ?>
      <div class="table-wrap"><table class="data-table"><thead><tr><th>Médicament</th><th>Substance (DCI)</th><th>Forme & Dosage</th><th>Quantité</th></tr></thead><tbody>
      <?php foreach ($detail['stock'] as $st): ?>
        <tr><td><strong><?= e($st['name']) ?></strong></td><td><?= e($st['dci'] ?? '—') ?></td><td><?= e($st['form'] . ' ' . ($st['dosage'] ?? '')) ?></td><td><span class="badge-inline badge-ok"><?= (int)$st['quantity'] ?> en stock</span></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($detail['analyses'])): ?>
  <section class="panel">
    <div class="panel-heading"><h2>Analyses biologiques proposées par ce laboratoire</h2><a class="text-link" href="index.php?page=laboratoires">Voir toutes les analyses →</a></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Analyse</th><th>Échantillon</th><th>Délai</th><th>À jeun</th><th>Tarif</th></tr></thead><tbody>
      <?php foreach ($detail['analyses'] as $an): ?>
        <tr><td><strong><?= e($an['nom']) ?></strong><small><?= e($an['description']) ?></small></td><td><?= e($an['type_echantillon']) ?></td><td><?= (int)$an['delai_heures'] ?> h</td><td><?= (int)$an['a_jeun'] ? 'Oui' : 'Non' ?></td><td><strong><?= number_format((float)$an['prix'], 2, ',', ' ') ?> DT</strong></td></tr>
      <?php endforeach; ?>
    </tbody></table></div>
  </section>
  <?php endif; ?>

  <?php if (!empty($detail['collectes_ville'])): ?>
  <section class="panel">
    <div class="panel-heading"><h2>Collectes BloodLink à <?= e($detail['ville']) ?></h2><a class="text-link" href="index.php?page=collectes">Toutes les collectes →</a></div>
    <?php foreach ($detail['collectes_ville'] as $cv): ?>
      <div class="slot-row">
        <div><strong><?= e($cv['titre']) ?></strong><small><?= e(date('d/m/Y H:i', strtotime($cv['debut']))) ?> – <?= e(date('H:i', strtotime($cv['fin']))) ?></small></div>
        <a class="button button-outline button-small" href="index.php?page=collecte&id=<?= (int)$cv['id'] ?>">Réserver un don →</a>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

<?php elseif ($page === 'rendez-vous'):
  $preselectPrat = (int)($_GET['praticien'] ?? $_POST['id_praticien'] ?? 0);
?>
  <div class="page-intro">
    <div>
      <span class="eyebrow">MODULE 2 · PRATICIENS & RENDEZ-VOUS MÉDICAUX</span>
      <h1>Prendre un rendez-vous médical</h1>
      <p>Choisissez un praticien rattaché à un établissement de l’annuaire et réservez un horaire en format 24h.</p>
    </div>
    <?php if ($current && in_array($current['role'], ['ADMIN', 'RESPONSABLE_RDV'], true)): ?>
      <a class="button button-primary" href="index.php?page=admin-rendez-vous">Gérer les RDV</a>
    <?php endif; ?>
  </div>

  <div class="detail-grid">
    <section class="panel" style="margin-top:0">
      <div class="panel-heading">
        <h2>Praticiens disponibles</h2>
        <form method="get" action="index.php" class="inline-role-form">
          <input type="hidden" name="page" value="rendez-vous">
          <select name="specialite" onchange="this.form.submit()">
            <option value="">Toutes les spécialités</option>
            <?php foreach ($moduleData['specialites'] ?? [] as $sp): ?>
              <option value="<?= e($sp) ?>" <?= ($_GET['specialite'] ?? '') === $sp ? 'selected' : '' ?>><?= e($sp) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
      <div class="clinical-list">
        <?php foreach ($moduleData['praticiens'] ?? [] as $pr): ?>
          <article class="clinical-row-card">
            <div class="clinical-row-info">
              <div class="clinical-row-title">
                <strong>Dr <?= e($pr['prenom'] . ' ' . $pr['nom']) ?></strong>
                <span class="pill"><?= e($pr['specialite']) ?></span>
              </div>
              <p class="muted">
                <?php if (!empty($pr['etablissement_nom'])): ?>
                  🏥 <a class="text-link" href="index.php?page=annuaire-detail&id=<?= (int)$pr['etablissement_id'] ?>"><?= e($pr['etablissement_nom']) ?> (<?= e($pr['etablissement_ville'] ?? '') ?>)</a>
                <?php elseif (!empty($pr['adresse'])): ?>
                  📍 <?= e($pr['adresse']) ?>
                <?php endif; ?>
                <?= !empty($pr['telephone']) ? ' · 📞 ' . e($pr['telephone']) : '' ?>
              </p>
            </div>
            <a class="button button-outline button-small" href="index.php?page=rendez-vous&praticien=<?= (int)$pr['id_praticien'] ?>#form-rdv">Choisir</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <aside class="form-panel" id="form-rdv" style="margin:0">
      <span class="eyebrow">RÉSERVATION EN LIGNE</span>
      <h2 style="margin-top:6px">Nouveau rendez-vous</h2>
      <?php if (!$current): ?>
        <p class="muted">Connectez-vous à votre compte utilisateur pour réserver et suivre vos rendez-vous médicaux.</p>
        <a class="button button-primary" href="index.php?page=connexion">Se connecter pour réserver</a>
      <?php else: ?>
        <form method="post" action="index.php?page=rdv-reserver" data-validate="rendez-vous" class="compact-form">
          <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
          <div class="field">
            <label for="rdv-prat">Praticien</label>
            <select id="rdv-prat" name="id_praticien">
              <option value="">— Choisir un médecin —</option>
              <?php foreach ($moduleData['praticiens'] ?? [] as $pr): ?>
                <option value="<?= (int)$pr['id_praticien'] ?>" <?= $preselectPrat === (int)$pr['id_praticien'] ? 'selected' : '' ?>>
                  Dr <?= e($pr['prenom'] . ' ' . $pr['nom']) ?> — <?= e($pr['specialite']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="rdv-date">Date du rendez-vous</label>
            <input id="rdv-date" name="date_rendezvous" type="date" value="<?= e($_POST['date_rendezvous'] ?? date('Y-m-d', strtotime('+1 day'))) ?>">
          </div>
          <div class="field">
            <label for="rdv-heure">Horaire (format 24h)</label>
            <select id="rdv-heure" name="heure_rendezvous">
              <?php for ($h = 8; $h <= 17; $h++): foreach (['00', '30'] as $m): $tVal = sprintf('%02d:%s', $h, $m); ?>
                <option value="<?= $tVal ?>" <?= ($_POST['heure_rendezvous'] ?? '10:00') === $tVal ? 'selected' : '' ?>><?= $tVal ?></option>
              <?php endforeach; endfor; ?>
            </select>
          </div>
          <div class="field">
            <label for="rdv-motif">Motif de la consultation</label>
            <textarea id="rdv-motif" name="motif" rows="3" placeholder="Ex. Contrôle tension, bilan pré-don, consultation cardiologie…"><?= e($_POST['motif'] ?? '') ?></textarea>
          </div>
          <button class="button button-primary">Confirmer la demande de RDV</button>
        </form>
      <?php endif; ?>
    </aside>
  </div>

  <?php if ($current): ?>
  <section class="panel">
    <div class="panel-heading"><h2>Mes rendez-vous médicaux</h2><span class="pill"><?= count($moduleData['mesRdv'] ?? []) ?></span></div>
    <?php if (!empty($moduleData['mesRdv'])): ?>
      <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Date & Heure (24h)</th><th>Praticien & Établissement</th><th>Motif</th><th>Statut</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($moduleData['mesRdv'] as $r): ?>
          <tr>
            <td><strong><?= e(date('d/m/Y', strtotime($r['date_rendezvous']))) ?> à <?= e(substr((string)$r['heure_rendezvous'], 0, 5)) ?></strong></td>
            <td><strong>Dr <?= e($r['praticien_prenom'] . ' ' . $r['praticien_nom']) ?></strong><small><?= e($r['specialite']) ?><?= !empty($r['etablissement_nom']) ? ' · ' . e($r['etablissement_nom']) : '' ?></small></td>
            <td><?= e($r['motif']) ?></td>
            <td><span class="badge-inline <?= $r['statut']==='Confirmé'?'badge-ok':($r['statut']==='Annulé'?'badge-danger':'badge-warn') ?>"><?= e($r['statut']) ?></span></td>
            <td>
              <?php if ($r['statut'] !== 'Annulé' && $r['statut'] !== 'Terminé'): ?>
                <form method="post" action="index.php?page=rdv-annuler" data-confirm="Annuler ce rendez-vous ?">
                  <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$r['id_rendezvous'] ?>">
                  <button class="button button-quiet button-small">Annuler</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php else: ?>
      <p class="muted">Vous n’avez pas encore pris de rendez-vous médical.</p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

<?php elseif ($page === 'pharmacies'):
  $qMed = trim((string)($_GET['q'] ?? ''));
  $citySel = trim((string)($_GET['city'] ?? ''));
  $onDutySel = !empty($_GET['on_duty']);
?>
  <div class="page-intro">
    <div>
      <span class="eyebrow">MODULE 3 · PHARMACIES DE GARDE & DISPONIBILITÉ MÉDICAMENTS</span>
      <h1>Pharmacies & Recherche de médicaments</h1>
      <p>Trouvez les pharmacies de garde aujourd’hui et vérifiez les stocks de médicaments en direct (reliés à l’Annuaire Médical).</p>
    </div>
    <?php if ($current && in_array($current['role'], ['ADMIN', 'RESPONSABLE_PHARMACIE'], true)): ?>
      <a class="button button-primary" href="index.php?page=admin-pharmacies">Gérer les pharmacies</a>
    <?php endif; ?>
  </div>

  <section class="filter-bar-panel">
    <form method="get" action="index.php" class="filter-bar-grid">
      <input type="hidden" name="page" value="pharmacies">
      <div class="field">
        <label for="ph-q">Médicament ou substance active (DCI)</label>
        <input id="ph-q" name="q" value="<?= e($qMed) ?>" placeholder="Ex. Doliprane, Paracétamol, Tardyferon…">
      </div>
      <div class="field">
        <label for="ph-city">Ville</label>
        <select id="ph-city" name="city">
          <option value="">Toutes les villes</option>
          <?php foreach ($moduleData['cities'] ?? [] as $c): ?>
            <option value="<?= e($c) ?>" <?= $citySel === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="checkbox-line" style="margin-top:26px">
          <input type="checkbox" name="on_duty" value="1" <?= $onDutySel ? 'checked' : '' ?>>
          <span>De garde aujourd’hui uniquement</span>
        </label>
      </div>
      <div class="filter-bar-actions">
        <button class="button button-primary button-small">Rechercher</button>
        <a class="button button-quiet button-small" href="index.php?page=pharmacies">Réinitialiser</a>
      </div>
    </form>
  </section>

  <?php if ($qMed !== ''): ?>
  <section class="panel">
    <div class="panel-heading"><h2>Disponibilité en stock pour « <?= e($qMed) ?> »</h2><span class="pill"><?= count($moduleData['stockResults'] ?? []) ?> résultat(s)</span></div>
    <?php if (!empty($moduleData['stockResults'])): ?>
      <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Pharmacie</th><th>Médicament</th><th>Forme & Dosage</th><th>Stock</th><th>Garde</th></tr></thead>
        <tbody>
        <?php foreach ($moduleData['stockResults'] as $sr): ?>
          <tr>
            <td><strong><a class="text-link" href="index.php?page=annuaire-detail&id=<?= (int)$sr['id'] ?>"><?= e($sr['nom']) ?></a></strong><small><?= e($sr['ville']) ?> · <?= e($sr['adresse']) ?><?= !empty($sr['telephone']) ? ' · 📞 ' . e($sr['telephone']) : '' ?></small></td>
            <td><strong><?= e($sr['med_name']) ?></strong><small><?= e($sr['dci'] ?? '') ?></small></td>
            <td><?= e($sr['form'] . ' ' . ($sr['dosage'] ?? '')) ?> <?= (int)$sr['requires_prescription'] ? '<span class="badge-inline badge-warn">Ordonnance</span>' : '' ?></td>
            <td><span class="badge-inline badge-ok"><?= (int)$sr['quantity'] ?> boîte(s)</span></td>
            <td><?= !empty($sr['duty_until']) ? '<span class="badge-inline badge-ok">✓ De garde jusqu’au ' . e(date('d/m/Y', strtotime($sr['duty_until']))) . '</span>' : '<span class="muted">Horaire normal</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php else: ?>
      <p class="muted">Aucune pharmacie ne déclare ce médicament en stock selon vos filtres.</p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <div class="card-grid" style="margin-top:18px">
    <?php foreach ($moduleData['pharmacies'] ?? [] as $ph): ?>
    <article class="panel module-card">
      <div class="card-badges">
        <span class="badge-inline badge-neutral">📍 <?= e($ph['ville']) ?></span>
        <?php if (!empty($ph['duty_until'])): ?>
          <span class="badge-inline badge-ok">✓ De garde jusqu’au <?= e(date('d/m/Y', strtotime($ph['duty_until']))) ?></span>
        <?php else: ?>
          <span class="badge-inline badge-neutral">Ouverture standard</span>
        <?php endif; ?>
      </div>
      <h2 style="margin:12px 0 6px;font-size:1.15rem"><?= e($ph['nom']) ?></h2>
      <p class="muted" style="margin:0 0 12px"><?= e($ph['adresse']) ?></p>
      <div class="card-foot">
        <span><?= (int)($ph['nb_meds_dispo'] ?? 0) ?> médicament(s) en stock</span>
        <a class="button button-outline button-small" href="index.php?page=annuaire-detail&id=<?= (int)$ph['id'] ?>">Voir le stock →</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

<?php elseif ($page === 'laboratoires'): ?>
  <div class="page-intro">
    <div>
      <span class="eyebrow">MODULE 4 · LABORATOIRES & ANALYSES MÉDICALES</span>
      <h1>Analyses biologiques & Comptes rendus</h1>
      <p>Consultez le catalogue des analyses médicales (tarifs, délais, conditions à jeun) et accédez à vos comptes rendus validés.</p>
    </div>
    <?php if ($current && in_array($current['role'], ['ADMIN', 'RESPONSABLE_LABO'], true)): ?>
      <a class="button button-primary" href="index.php?page=admin-laboratoires">Gérer le laboratoire</a>
    <?php endif; ?>
  </div>

  <section class="panel" style="margin-top:0">
    <div class="panel-heading">
      <h2>Catalogue des analyses médicales</h2>
      <form method="get" action="index.php" class="inline-role-form">
        <input type="hidden" name="page" value="laboratoires">
        <select name="type" onchange="this.form.submit()">
          <option value="">Tous les échantillons</option>
          <?php foreach (AnalyseMedicale::TYPES_ECHANTILLON as $te): ?>
            <option value="<?= e($te) ?>" <?= ($_GET['type'] ?? '') === $te ? 'selected' : '' ?>><?= e($te) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Analyse</th><th>Laboratoire</th><th>Échantillon</th><th>À jeun</th><th>Délai</th><th>Prix</th></tr></thead>
      <tbody>
      <?php foreach ($moduleData['analyses'] ?? [] as $an): ?>
        <tr>
          <td><strong><?= e($an['nom']) ?></strong><small><?= e($an['description']) ?></small></td>
          <td>
            <?php if (!empty($an['laboratoire_nom'])): ?>
              <a class="text-link" href="index.php?page=annuaire-detail&id=<?= (int)$an['etablissement_id'] ?>"><?= e($an['laboratoire_nom']) ?></a>
              <small><?= e($an['laboratoire_ville'] ?? '') ?></small>
            <?php else: ?>
              <span class="muted">Laboratoire partenaire</span>
            <?php endif; ?>
          </td>
          <td><span class="pill"><?= e($an['type_echantillon']) ?></span></td>
          <td><?= (int)$an['a_jeun'] ? '<span class="badge-inline badge-warn">À jeun requis</span>' : '<span class="badge-inline badge-ok">Non requis</span>' ?></td>
          <td><?= (int)$an['delai_heures'] ?> h</td>
          <td><strong><?= number_format((float)$an['prix'], 2, ',', ' ') ?> DT</strong></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </section>

  <section class="panel">
    <div class="panel-heading">
      <div>
        <h2><?= $current ? 'Mes comptes rendus d’analyses' : 'Consulter vos comptes rendus' ?></h2>
        <p class="muted">Vos résultats biologiques sont rattachés de façon sécurisée à votre compte utilisateur.</p>
      </div>
    </div>
    <?php if (!$current): ?>
      <p class="muted">Connectez-vous pour consulter vos comptes rendus d’analyses médicales en toute confidentialité.</p>
      <a class="button button-primary" href="index.php?page=connexion">Se connecter</a>
    <?php elseif (!empty($moduleData['mesComptesRendus'])): ?>
      <div class="clinical-list">
        <?php foreach ($moduleData['mesComptesRendus'] as $cr): ?>
          <article class="clinical-row-card">
            <div class="clinical-row-info" style="flex:1">
              <div class="clinical-row-title">
                <strong><?= e($cr['analyse_nom']) ?></strong>
                <span class="badge-inline <?= $cr['interpretation']==='Normal'?'badge-ok':'badge-danger' ?>"><?= e($cr['interpretation']) ?></span>
                <span class="muted">Prélèvement du <?= e(date('d/m/Y', strtotime($cr['date_prelevement']))) ?></span>
              </div>
              <p style="margin:8px 0 4px;white-space:pre-line;color:#1e293b"><?= e($cr['resultat']) ?></p>
              <?php if (!empty($cr['commentaire'])): ?><p class="muted"><strong>Note du biologiste :</strong> <?= e($cr['commentaire']) ?></p><?php endif; ?>
              <?php if (!empty($cr['laboratoire_nom'])): ?><p class="muted">Laboratoire : <strong><?= e($cr['laboratoire_nom']) ?></strong></p><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">Aucun compte rendu d’analyse n’est encore associé à votre compte.</div>
    <?php endif; ?>
  </section>
<?php endif; ?>
</div>
