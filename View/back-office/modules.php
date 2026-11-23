<?php
/**
 * Vues Back-Office (ADMIN) des 4 modules intégrés :
 * - admin-annuaire : CRUD Établissements de santé & Services médicaux
 * - admin-rendez-vous : CRUD Praticiens & gestion des Rendez-vous
 * - admin-pharmacies : CRUD Médicaments, Gardes & Stocks (Disponibilités)
 * - admin-laboratoires : CRUD Analyses médicales & Comptes rendus
 */
?>
<?php if ($page === 'admin-annuaire'):
  $etabs = $moduleData['etablissements'] ?? [];
  $services = $moduleData['services'] ?? [];
?>
<div class="workspace-tabs" role="tablist" data-workspace-tabs>
  <button type="button" class="wtab active" role="tab" data-tab-target="tab-etabs" aria-selected="true"><span>1 · Établissements de santé</span><b class="wtab-count"><?= count($etabs) ?></b></button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-services" aria-selected="false"><span>2 · Services médicaux</span><b class="wtab-count"><?= count($services) ?></b></button>
</div>

<section id="tab-etabs" class="wtab-panel active" role="tabpanel">
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Ajouter un établissement (Hôpital, Clinique, Pharmacie, Laboratoire)</h2></div>
    <form method="post" action="index.php?page=admin-etab-sauver" data-validate="etablissement" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field"><label for="et-nom">Nom de l’établissement</label><input id="et-nom" name="nom" placeholder="Ex. Clinique Carthage"></div>
        <div class="field">
          <label for="et-type">Type</label>
          <select id="et-type" name="type">
            <?php foreach (EtablissementSante::TYPES as $k => $lbl): ?><option value="<?= e($k) ?>"><?= e($lbl) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="et-ville">Ville</label><input id="et-ville" name="ville" placeholder="Ex. Tunis"></div>
        <div class="field"><label for="et-adr">Adresse</label><input id="et-adr" name="adresse" placeholder="Rue, avenue…"></div>
        <div class="field"><label for="et-tel">Téléphone</label><input id="et-tel" name="telephone" placeholder="71 000 000"></div>
        <div class="field"><label for="et-mail">E-mail</label><input id="et-mail" name="email" type="email" placeholder="contact@etablissement.tn"></div>
        <div class="field full"><label for="et-desc">Description</label><input id="et-desc" name="description" placeholder="Présentation courte de l’établissement"></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Ajouter l’établissement</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Établissements enregistrés</h2><div class="search-box"><input type="search" data-table-search="#etab-table" placeholder="Rechercher un établissement…"></div></div>
    <div class="table-wrap"><table class="data-table" id="etab-table">
      <thead><tr><th>Établissement</th><th>Type</th><th>Ville & Adresse</th><th>Contact</th><th>Liaisons</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($etabs as $et): ?>
        <tr data-search-row>
          <td><strong><?= e($et['nom']) ?></strong></td>
          <td><span class="pill"><?= e(EtablissementSante::TYPES[$et['type']] ?? $et['type']) ?></span></td>
          <td><strong><?= e($et['ville']) ?></strong><small><?= e($et['adresse']) ?></small></td>
          <td><?= e($et['telephone'] ?? '—') ?><small><?= e($et['email'] ?? '') ?></small></td>
          <td><small><?= (int)$et['nb_services'] ?> serv. · <?= (int)$et['nb_praticiens'] ?> prat. · <?= (int)$et['nb_analyses'] ?> anal.</small></td>
          <td class="table-actions">
            <a class="button button-quiet button-small" href="index.php?page=annuaire-detail&id=<?= (int)$et['id'] ?>">Voir</a>
            <form method="post" action="index.php?page=admin-etab-supprimer" data-confirm="Supprimer cet établissement ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$et['id'] ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<section id="tab-services" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Ajouter un service médical interne</h2></div>
    <form method="post" action="index.php?page=admin-service-sauver" data-validate="service-medical" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field">
          <label for="srv-etab">Établissement</label>
          <select id="srv-etab" name="etablissement_id">
            <option value="">— Choisir —</option>
            <?php foreach ($etabs as $et): ?><option value="<?= (int)$et['id'] ?>"><?= e($et['nom']) ?> (<?= e($et['ville']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="srv-nom">Nom du service</label><input id="srv-nom" name="nom" placeholder="Ex. Cardiologie"></div>
        <div class="field"><label for="srv-spec">Spécialité</label><input id="srv-spec" name="specialite" placeholder="Ex. Cardiologie interventionnelle"></div>
        <div class="field"><label for="srv-hor">Horaires</label><input id="srv-hor" name="horaires" placeholder="Ex. Lun–Sam 08:00–17:00"></div>
        <div class="field"><label for="srv-tel">Téléphone direct</label><input id="srv-tel" name="telephone" placeholder="71 000 111"></div>
        <div class="field"><label class="checkbox-line" style="margin-top:26px"><input type="checkbox" name="est_urgence" value="1"><span>Service d’urgences 24/7</span></label></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Ajouter le service</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Liste des services médicaux</h2></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Service</th><th>Établissement</th><th>Spécialité & Horaires</th><th>Urgences</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><strong><?= e($s['nom']) ?></strong></td>
          <td><?= e($s['etablissement_nom']) ?><small><?= e($s['etablissement_ville']) ?></small></td>
          <td><?= e($s['specialite']) ?><small><?= e($s['horaires'] ?? '—') ?></small></td>
          <td><?= (int)$s['est_urgence'] ? '<span class="badge-inline badge-danger">Urgences 24/7</span>' : '<span class="badge-inline badge-neutral">Standard</span>' ?></td>
          <td>
            <form method="post" action="index.php?page=admin-service-supprimer" data-confirm="Supprimer ce service ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<?php elseif ($page === 'admin-rendez-vous'):
  $rdvs = $moduleData['rendezVous'] ?? [];
  $praticiens = $moduleData['praticiens'] ?? [];
  $etabs = $moduleData['etablissements'] ?? [];
?>
<div class="workspace-tabs" role="tablist" data-workspace-tabs>
  <button type="button" class="wtab active" role="tab" data-tab-target="tab-rdv-list" aria-selected="true"><span>1 · Rendez-vous réservés</span><b class="wtab-count"><?= count($rdvs) ?></b></button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-praticiens" aria-selected="false"><span>2 · Praticiens</span><b class="wtab-count"><?= count($praticiens) ?></b></button>
</div>

<section id="tab-rdv-list" class="wtab-panel active" role="tabpanel">
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Suivi et validation des rendez-vous</h2><div class="search-box"><input type="search" data-table-search="#rdv-table" placeholder="Rechercher un patient ou praticien…"></div></div>
    <div class="table-wrap"><table class="data-table" id="rdv-table">
      <thead><tr><th>Date & Heure</th><th>Patient</th><th>Praticien & Établissement</th><th>Motif</th><th>Statut & Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rdvs as $r): ?>
        <tr data-search-row>
          <td><strong><?= e(date('d/m/Y', strtotime($r['date_rendezvous']))) ?> · <?= e(substr((string)$r['heure_rendezvous'], 0, 5)) ?></strong></td>
          <td><strong><?= e($r['patient_nom'] ?: ($r['compte_nom'] ?? 'Patient')) ?></strong><small><?= e($r['compte_email'] ?? $r['patient_telephone'] ?? '') ?></small></td>
          <td><strong>Dr <?= e($r['praticien_prenom'] . ' ' . $r['praticien_nom']) ?></strong><small><?= e($r['specialite']) ?><?= !empty($r['etablissement_nom']) ? ' · ' . e($r['etablissement_nom']) : '' ?></small></td>
          <td><?= e($r['motif']) ?></td>
          <td class="table-actions">
            <form method="post" action="index.php?page=admin-rdv-statut" class="inline-role-form">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id_rendezvous'] ?>">
              <select name="statut">
                <?php foreach (RendezVous::STATUTS as $st): ?><option value="<?= e($st) ?>" <?= $r['statut'] === $st ? 'selected' : '' ?>><?= e($st) ?></option><?php endforeach; ?>
              </select>
              <button class="button button-outline button-small">Mettre à jour</button>
            </form>
            <form method="post" action="index.php?page=admin-rdv-supprimer" data-confirm="Supprimer ce rendez-vous ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id_rendezvous'] ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<section id="tab-praticiens" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Ajouter un praticien (lié à l’Annuaire Médical)</h2></div>
    <form method="post" action="index.php?page=admin-praticien-sauver" data-validate="praticien" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field"><label for="pr-nom">Nom</label><input id="pr-nom" name="nom" placeholder="Ex. Mansouri"></div>
        <div class="field"><label for="pr-prenom">Prénom</label><input id="pr-prenom" name="prenom" placeholder="Ex. Youssef"></div>
        <div class="field"><label for="pr-spec">Spécialité</label><input id="pr-spec" name="specialite" placeholder="Ex. Hématologie"></div>
        <div class="field">
          <label for="pr-etab">Établissement rattaché</label>
          <select id="pr-etab" name="etablissement_id">
            <option value="">— Cabinet indépendant —</option>
            <?php foreach ($etabs as $et): ?><option value="<?= (int)$et['id'] ?>"><?= e($et['nom']) ?> (<?= e($et['ville']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="pr-tel">Téléphone</label><input id="pr-tel" name="telephone" placeholder="71 000 000"></div>
        <div class="field"><label for="pr-mail">E-mail</label><input id="pr-mail" name="email" type="email" placeholder="dr@clinique.tn"></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Ajouter le praticien</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Praticiens enregistrés</h2></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Praticien</th><th>Spécialité</th><th>Établissement</th><th>Contact</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($praticiens as $p): ?>
        <tr>
          <td><strong>Dr <?= e($p['prenom'] . ' ' . $p['nom']) ?></strong></td>
          <td><span class="pill"><?= e($p['specialite']) ?></span></td>
          <td><?= e($p['etablissement_nom'] ?? $p['adresse'] ?? '—') ?></td>
          <td><?= e($p['telephone'] ?? '—') ?><small><?= e($p['email'] ?? '') ?></small></td>
          <td>
            <form method="post" action="index.php?page=admin-praticien-supprimer" data-confirm="Supprimer ce praticien ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$p['id_praticien'] ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<?php elseif ($page === 'admin-pharmacies'):
  $pharmacies = $moduleData['pharmacies'] ?? [];
  $meds = $moduleData['medicaments'] ?? [];
  $gardes = $moduleData['gardes'] ?? [];
  $dispos = $moduleData['disponibilites'] ?? [];
?>
<div class="workspace-tabs" role="tablist" data-workspace-tabs>
  <button type="button" class="wtab active" role="tab" data-tab-target="tab-meds" aria-selected="true"><span>1 · Médicaments</span><b class="wtab-count"><?= count($meds) ?></b></button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-gardes" aria-selected="false"><span>2 · Gardes de pharmacie</span><b class="wtab-count"><?= count($gardes) ?></b></button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-stock" aria-selected="false"><span>3 · Stock / Disponibilités</span><b class="wtab-count"><?= count($dispos) ?></b></button>
</div>

<section id="tab-meds" class="wtab-panel active" role="tabpanel">
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Ajouter un médicament au catalogue</h2></div>
    <form method="post" action="index.php?page=admin-med-sauver" data-validate="medicament" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field"><label for="m-nom">Nom commercial</label><input id="m-nom" name="name" placeholder="Ex. Doliprane"></div>
        <div class="field"><label for="m-dci">Substance active (DCI)</label><input id="m-dci" name="dci" placeholder="Ex. Paracétamol"></div>
        <div class="field"><label for="m-form">Forme</label><input id="m-form" name="form" placeholder="Ex. Comprimé, Sirop"></div>
        <div class="field"><label for="m-dos">Dosage</label><input id="m-dos" name="dosage" placeholder="Ex. 1000 mg"></div>
        <div class="field full"><label class="checkbox-line"><input type="checkbox" name="requires_prescription" value="1"><span>Ordonnance médicale requise</span></label></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Ajouter le médicament</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Catalogue des médicaments</h2></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Nom</th><th>DCI</th><th>Forme & Dosage</th><th>Ordonnance</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($meds as $m): ?>
        <tr>
          <td><strong><?= e($m->getName()) ?></strong></td>
          <td><?= e($m->getDci() ?? '—') ?></td>
          <td><?= e($m->getForm() . ' ' . ($m->getDosage() ?? '')) ?></td>
          <td><?= $m->getRequiresPrescription() ? '<span class="badge-inline badge-warn">Oui</span>' : '<span class="badge-inline badge-ok">Libre</span>' ?></td>
          <td>
            <form method="post" action="index.php?page=admin-med-supprimer" data-confirm="Supprimer ce médicament ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= $m->getId() ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<section id="tab-gardes" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Planifier une garde de pharmacie</h2></div>
    <form method="post" action="index.php?page=admin-garde-sauver" data-validate="garde" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field">
          <label for="g-etab">Pharmacie (depuis l’Annuaire)</label>
          <select id="g-etab" name="etablissement_id">
            <option value="">— Choisir une pharmacie —</option>
            <?php foreach ($pharmacies as $ph): ?><option value="<?= $ph->getId() ?>"><?= e($ph->getNom()) ?> (<?= e($ph->getVille()) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="g-notes">Notes</label><input id="g-notes" name="notes" placeholder="Ex. Garde de nuit"></div>
        <div class="field"><label for="g-start">Date de début</label><input id="g-start" name="start_date" type="date" value="<?= date('Y-m-d') ?>"></div>
        <div class="field"><label for="g-end">Date de fin</label><input id="g-end" name="end_date" type="date" value="<?= date('Y-m-d', strtotime('+6 days')) ?>"></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Enregistrer la garde</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Périodes de garde enregistrées</h2></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Pharmacie</th><th>Début</th><th>Fin</th><th>Notes</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($gardes as $g): ?>
        <tr>
          <td><strong><?= e($g->getEtablissementName()) ?></strong></td>
          <td><?= e(date('d/m/Y', strtotime($g->getStartDate()))) ?></td>
          <td><?= e(date('d/m/Y', strtotime($g->getEndDate()))) ?></td>
          <td><?= e($g->getNotes() ?? '—') ?></td>
          <td>
            <form method="post" action="index.php?page=admin-garde-supprimer" data-confirm="Supprimer cette garde ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= $g->getId() ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<section id="tab-stock" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Mettre à jour un stock (Pharmacie × Médicament)</h2></div>
    <form method="post" action="index.php?page=admin-stock-sauver" data-validate="stock" class="inline-fields">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="field">
        <label for="st-ph">Pharmacie</label>
        <select id="st-ph" name="etablissement_id">
          <?php foreach ($pharmacies as $ph): ?><option value="<?= $ph->getId() ?>"><?= e($ph->getNom()) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="st-med">Médicament</label>
        <select id="st-med" name="medicament_id">
          <?php foreach ($meds as $m): ?><option value="<?= $m->getId() ?>"><?= e($m->getName() . ' ' . ($m->getDosage() ?? '')) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label for="st-qty">Quantité</label><input id="st-qty" name="quantity" type="number" min="0" value="10"></div>
      <button class="button button-primary">Enregistrer le stock</button>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Stocks déclarés</h2><div class="search-box"><input type="search" data-table-search="#stock-table" placeholder="Filtrer par pharmacie ou médicament…"></div></div>
    <div class="table-wrap"><table class="data-table" id="stock-table">
      <thead><tr><th>Pharmacie</th><th>Médicament</th><th>Quantité</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($dispos as $d): ?>
        <tr data-search-row>
          <td><strong><?= e($d->getEtablissementName()) ?></strong></td>
          <td><?= e($d->getMedicamentName()) ?></td>
          <td><span class="badge-inline <?= $d->getQuantity() > 0 ? 'badge-ok' : 'badge-danger' ?>"><?= $d->getQuantity() ?> boîte(s)</span></td>
          <td>
            <form method="post" action="index.php?page=admin-stock-supprimer" data-confirm="Retirer cette ligne de stock ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= $d->getId() ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<?php elseif ($page === 'admin-laboratoires'):
  $analyses = $moduleData['analyses'] ?? [];
  $labos = $moduleData['laboratoires'] ?? [];
  $crs = $moduleData['comptesRendus'] ?? [];
  $allUsers = $moduleData['utilisateurs'] ?? [];
?>
<div class="workspace-tabs" role="tablist" data-workspace-tabs>
  <button type="button" class="wtab active" role="tab" data-tab-target="tab-analyses" aria-selected="true"><span>1 · Analyses médicales</span><b class="wtab-count"><?= count($analyses) ?></b></button>
  <button type="button" class="wtab" role="tab" data-tab-target="tab-cr" aria-selected="false"><span>2 · Comptes rendus patients</span><b class="wtab-count"><?= count($crs) ?></b></button>
</div>

<section id="tab-analyses" class="wtab-panel active" role="tabpanel">
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Ajouter une analyse médicale (liée à un laboratoire)</h2></div>
    <form method="post" action="index.php?page=admin-analyse-sauver" data-validate="analyse" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field"><label for="an-nom">Nom de l’analyse</label><input id="an-nom" name="nom" placeholder="Ex. Bilan hépatique"></div>
        <div class="field">
          <label for="an-lab">Laboratoire</label>
          <select id="an-lab" name="etablissement_id">
            <option value="">— Laboratoire partenaire —</option>
            <?php foreach ($labos as $lb): ?><option value="<?= (int)$lb['id'] ?>"><?= e($lb['nom']) ?> (<?= e($lb['ville']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="an-type">Type d’échantillon</label>
          <select id="an-type" name="type_echantillon">
            <?php foreach (AnalyseMedicale::TYPES_ECHANTILLON as $te): ?><option value="<?= e($te) ?>"><?= e($te) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="an-prix">Prix (DT)</label><input id="an-prix" name="prix" type="number" step="0.5" placeholder="25.00"></div>
        <div class="field"><label for="an-delai">Délai de résultat (heures)</label><input id="an-delai" name="delai_heures" type="number" value="24"></div>
        <div class="field"><label class="checkbox-line" style="margin-top:26px"><input type="checkbox" name="a_jeun" value="1"><span>Patient à jeun requis</span></label></div>
        <div class="field full"><label for="an-desc">Description</label><input id="an-desc" name="description" placeholder="Paramètres mesurés par cette analyse…"></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Ajouter l’analyse</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Catalogue des analyses</h2></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Analyse</th><th>Laboratoire</th><th>Échantillon</th><th>Prix & Délai</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($analyses as $an): ?>
        <tr>
          <td><strong><?= e($an['nom']) ?></strong><small><?= e($an['description']) ?></small></td>
          <td><?= e($an['laboratoire_nom'] ?? '—') ?></td>
          <td><span class="pill"><?= e($an['type_echantillon']) ?></span></td>
          <td><strong><?= number_format((float)$an['prix'], 2, ',', ' ') ?> DT</strong> · <?= (int)$an['delai_heures'] ?> h</td>
          <td>
            <form method="post" action="index.php?page=admin-analyse-supprimer" data-confirm="Supprimer cette analyse ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$an['id'] ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>

<section id="tab-cr" class="wtab-panel" role="tabpanel" hidden>
  <div class="panel" style="margin-top:0">
    <div class="panel-heading"><h2>Publier un compte rendu d’analyse</h2></div>
    <form method="post" action="index.php?page=admin-cr-sauver" data-validate="compte-rendu" class="compact-form">
      <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
      <div class="form-grid">
        <div class="field">
          <label for="cr-an">Analyse réalisée</label>
          <select id="cr-an" name="id_analyse">
            <option value="">— Choisir —</option>
            <?php foreach ($analyses as $an): ?><option value="<?= (int)$an['id'] ?>"><?= e($an['nom']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="cr-user">Compte utilisateur associé (facultatif)</label>
          <select id="cr-user" name="utilisateur_id">
            <option value="">— Patient externe —</option>
            <?php foreach ($allUsers as $u): ?><option value="<?= (int)$u['id'] ?>"><?= e($u['nom']) ?> (<?= e($u['email']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="cr-pat">Nom du patient</label><input id="cr-pat" name="nom_patient" placeholder="Ex. Donneur Démo Un"></div>
        <div class="field"><label for="cr-date">Date de prélèvement</label><input id="cr-date" name="date_prelevement" type="date" value="<?= date('Y-m-d') ?>"></div>
        <div class="field">
          <label for="cr-int">Interprétation</label>
          <select id="cr-int" name="interpretation">
            <option value="Normal">Normal</option>
            <option value="Anormal">Anormal</option>
          </select>
        </div>
        <div class="field"><label for="cr-com">Commentaire du biologiste</label><input id="cr-com" name="commentaire" placeholder="Recommandation facultative"></div>
        <div class="field full"><label for="cr-res">Résultat détaillé</label><textarea id="cr-res" name="resultat" rows="2" placeholder="Valeurs mesurées…"></textarea></div>
      </div>
      <div class="form-actions"><button class="button button-primary">+ Enregistrer le compte rendu</button></div>
    </form>
  </div>

  <div class="panel">
    <div class="panel-heading"><h2>Comptes rendus enregistrés</h2></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Patient</th><th>Analyse</th><th>Date</th><th>Interprétation</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($crs as $cr): ?>
        <tr>
          <td><strong><?= e($cr['nom_patient']) ?></strong><small><?= e($cr['compte_email'] ?? '') ?></small></td>
          <td><strong><?= e($cr['analyse_nom']) ?></strong><small><?= e($cr['resultat']) ?></small></td>
          <td><?= e(date('d/m/Y', strtotime($cr['date_prelevement']))) ?></td>
          <td><span class="badge-inline <?= $cr['interpretation']==='Normal'?'badge-ok':'badge-danger' ?>"><?= e($cr['interpretation']) ?></span></td>
          <td>
            <form method="post" action="index.php?page=admin-cr-supprimer" data-confirm="Supprimer ce compte rendu ?">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><input type="hidden" name="id" value="<?= (int)$cr['id'] ?>">
              <button class="button button-danger button-small">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>
<?php endif; ?>
