<div class="page-intro"><div><span class="eyebrow"><?= ($current['role']??'')==='ADMIN'?'PORTAIL D’ADMINISTRATION':'ESPACE RESPONSABLE DE COLLECTE' ?></span><h1><?php
echo match($page) {
    'utilisateurs'=>'Utilisateurs et rôles',
    'utilisateur-formulaire'=>'Ajouter un compte donneur',
    'gestion-collectes'=>'Mes collectes de sang',
    'collecte-formulaire'=>'Configuration de la collecte',
    'collecte-gestion'=>'Poste de suivi de collecte',
    'tableau-bord'=>'Tableau de bord & Bilans des collectes',
    'gestion-sos'=>'Modération des alertes SOS',
    'parametres'=>'Configuration de mon centre',
    'inviter-responsable'=>'Inviter un responsable de collecte',
    'scan-donneur'=>'Vérification QR Donneur',
    'admin-annuaire'=>'Annuaire Médical & Services de santé',
    'admin-rendez-vous'=>'Praticiens & Rendez-vous médicaux',
    'admin-pharmacies'=>'Pharmacies, Médicaments, Gardes & Stocks',
    'admin-laboratoires'=>'Laboratoires, Analyses & Comptes rendus',
    'profil'=>'Mon compte et sécurité',
    default=>'Gestion'
}; ?></h1></div></div>
<?php foreach ($errors as $err): ?><p class="notice"><?= e($err) ?></p><?php endforeach; ?>
<?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?>

<?php if ($page==='utilisateurs'): ?>
<div class="toolbar-row">
  <div class="search-box">
    <input type="search" data-table-search="#users-table" placeholder="Rechercher un utilisateur par nom, e-mail ou rôle…">
  </div>
  <div class="toolbar-actions">
    <a class="button button-primary" href="index.php?page=inviter-responsable">✉ Inviter un responsable</a>
    <a class="button button-outline" href="index.php?page=utilisateur-formulaire">+ Ajouter un donneur</a>
  </div>
</div>
<div class="table-wrap">
  <table class="data-table" id="users-table">
    <thead><tr><th>Utilisateur</th><th>Adresse e-mail</th><th>Rôle actuel</th><th>État</th><th>Attribution du rôle & Statut</th></tr></thead>
    <tbody>
      <?php foreach ($items as $u): ?>
      <tr data-search-row>
        <td><strong>#<?= (int)$u['id'] ?> · <?= e($u['nom']) ?></strong></td>
        <td><?= e($u['email']) ?></td>
        <td><span class="role-badge role-badge-<?= e(mb_strtolower((string)$u['role'])) ?>"><?= e($u['role']) ?></span></td>
        <td><?= $u['actif'] ? '<span class="badge-inline badge-ok">Actif</span>' : '<span class="badge-inline badge-warn">Désactivé</span>' ?></td>
        <td class="table-actions">
          <?php if ((int)$u['id'] !== (int)$current['id']): ?>
            <form method="post" action="index.php?page=utilisateur-sauver" class="inline-role-form">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <select name="role" aria-label="Rôle de <?= e($u['nom']) ?>">
                <?php foreach (['DONNEUR','RESPONSABLE','ADMIN'] as $r): ?>
                  <option value="<?= $r ?>" <?= $u['role']===$r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
              <button class="button button-outline button-small">Changer rôle</button>
            </form>
            <form method="post" action="index.php?page=utilisateur-supprimer" data-confirm="<?= $u['actif'] ? 'Désactiver ce compte ?' : 'Réactiver ce compte ?' ?>">
              <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="button <?= $u['actif'] ? 'button-danger' : 'button-quiet' ?> button-small"><?= $u['actif'] ? 'Désactiver' : 'Réactiver' ?></button>
            </form>
          <?php else: ?>
            <span class="muted">Votre compte administrateur</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($page==='utilisateur-formulaire'): ?>
<div class="form-panel">
  <h2>Nouveau compte donneur</h2>
  <p class="muted">Créez un compte donneur. Pour un responsable de collecte, utilisez « Inviter un responsable ».</p>
  <form method="post" action="index.php?page=utilisateur-sauver" data-validate="user">
    <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
    <input type="hidden" name="id" value="0">
    <input type="hidden" name="role" value="DONNEUR">
    <div class="form-grid">
      <div class="field"><label for="nom">Nom complet du donneur</label><input id="nom" name="nom" value="<?= e($_POST['nom'] ?? '') ?>"></div>
      <div class="field"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" value="<?= e($_POST['email'] ?? '') ?>"></div>
      <div class="field full"><label for="mot_de_passe">Mot de passe initial (12 caractères minimum)</label><input id="mot_de_passe" name="mot_de_passe" type="password"></div>
      <div class="field full"><label class="checkbox-line"><input type="checkbox" name="actif" value="1" checked> Activer immédiatement le compte</label></div>
    </div>
    <div class="form-actions"><a class="button button-quiet" href="index.php?page=utilisateurs">Annuler</a><button class="button button-primary">Créer le compte donneur</button></div>
  </form>
</div>

<?php elseif ($page==='gestion-collectes'): ?>
<div class="filter-bar-panel" data-collecte-filter-root="#resp-collectes-table tbody tr[data-filter-item]">
  <div class="filter-bar-grid">
    <div class="field">
      <label for="resp-f-q">Recherche (Titre, ville, statut)</label>
      <input id="resp-f-q" type="search" data-filter-query placeholder="Ex. Tunis, Campus, PUBLIEE…">
    </div>
    <div class="field">
      <label for="resp-f-d1">Période : Du (date)</label>
      <input id="resp-f-d1" type="date" data-filter-date-from>
    </div>
    <div class="field">
      <label for="resp-f-d2">Au (date)</label>
      <input id="resp-f-d2" type="date" data-filter-date-to>
    </div>
    <div class="field">
      <label for="resp-f-t1">Horaire (24h) : De</label>
      <select id="resp-f-t1" data-filter-time-from>
        <option value="">Toute heure</option>
        <?php for ($h = 6; $h <= 22; $h++): $hm = sprintf('%02d:00', $h); ?>
          <option value="<?= $hm ?>"><?= $hm ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="field">
      <label for="resp-f-t2">À (format 24h)</label>
      <select id="resp-f-t2" data-filter-time-to>
        <option value="">Toute heure</option>
        <?php for ($h = 7; $h <= 23; $h++): $hm = sprintf('%02d:00', $h); ?>
          <option value="<?= $hm ?>"><?= $hm ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="filter-bar-actions">
      <button type="button" class="button button-quiet button-small" data-filter-reset>Réinitialiser</button>
      <a class="button button-primary button-small" href="index.php?page=collecte-formulaire">+ Nouvelle collecte</a>
    </div>
  </div>
</div>

<div class="table-wrap">
  <table class="data-table" id="resp-collectes-table">
    <thead><tr><th>Photo & Collecte</th><th>Période & Horaires (24h)</th><th>Statut</th><th>Participations</th><th>Volume total</th><th>Répartition par groupe (Sain confirmé vs Inconnu)</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($items as $c):
        $dStart = date('Y-m-d', strtotime($c['debut']));
        $dEnd = date('Y-m-d', strtotime($c['fin']));
        $tStart = date('H:i', strtotime($c['debut']));
        $tEnd = date('H:i', strtotime($c['fin']));
        $multiDay = ($dStart !== $dEnd);
      ?>
      <tr data-search-row data-filter-item data-start-date="<?= e($dStart) ?>" data-end-date="<?= e($dEnd) ?>" data-start-time="<?= e($tStart) ?>" data-end-time="<?= e($tEnd) ?>">
        <td>
          <div class="collecte-table-cell">
            <img class="collecte-thumb" src="<?= e($c['image_url'] ?? 'public/assets/img/collecte-centre.svg') ?>" alt="<?= e($c['titre']) ?>">
            <div>
              <strong><?= e($c['titre']) ?></strong>
              <small><?= e($c['ville']) ?> · <?= e($c['adresse']) ?></small>
            </div>
          </div>
        </td>
        <td>
          <?php if ($multiDay): ?>
            <strong>Du <?= e(date('d/m/Y',strtotime($c['debut']))) ?> au <?= e(date('d/m/Y',strtotime($c['fin']))) ?></strong>
            <small>Horaires 24h : <?= e($tStart) ?> – <?= e($tEnd) ?> (Longue durée)</small>
          <?php else: ?>
            <strong><?= e(date('d/m/Y',strtotime($c['debut']))) ?></strong>
            <small>Horaires 24h : <?= e($tStart) ?> – <?= e($tEnd) ?></small>
          <?php endif; ?>
        </td>
        <td><span class="pill <?= $c['statut']==='PUBLIEE'?'':($c['statut']==='BROUILLON'?'draft':'closed') ?>"><?= e($c['statut']) ?></span></td>
        <td><strong><?= (int)$c['participations'] ?></strong> participant(s)<small><?= (int)$c['dons'] ?> poche(s)</small></td>
        <td>
          <strong class="blood-volume-highlight"><?= number_format((int)($c['volume_ml']??0),0,',',' ') ?> ml</strong>
          <small><?= number_format((int)($c['volume_sain_ml']??0),0,',',' ') ?> ml sains · <?= number_format((int)($c['volume_inconnu_ml']??0),0,',',' ') ?> ml en attente</small>
        </td>
        <td>
          <?php if (!empty($c['groupes'])): ?>
            <div class="chip-list">
              <?php foreach ($c['groupes'] as $cg): ?>
                <?php if ($cg['etat'] === 'SAIN'): ?>
                  <span class="blood-chip blood-chip-sain"><strong>✓ <?= e($cg['groupe']) ?> (Sain)</strong> · <?= (int)$cg['volume_ml'] ?> ml (<?= (int)$cg['poches'] ?>)</span>
                <?php elseif ($cg['etat'] === 'ECARTE'): ?>
                  <span class="badge-inline badge-danger">✕ Écarté · <?= (int)$cg['volume_ml'] ?> ml</span>
                <?php else: ?>
                  <span class="badge-inline badge-warn">⏳ Groupe inconnu / Labo en attente · <?= (int)$cg['volume_ml'] ?> ml (<?= (int)$cg['poches'] ?>)</span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <span class="muted">Aucun sang prélevé</span>
          <?php endif; ?>
        </td>
        <td class="table-actions">
          <a class="button button-primary button-small" href="index.php?page=collecte-gestion&id=<?= (int)$c['id'] ?>">Suivre la collecte</a>
          <a class="button button-quiet button-small" href="index.php?page=collecte-formulaire&id=<?= (int)$c['id'] ?>">Photos & Horaires</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($page==='collecte-formulaire'): $c=$detail ?: [];
  $defaultDebut = date('Y-m-d\T09:00', strtotime('+1 day'));
  $defaultFin = date('Y-m-d\T14:00', strtotime('+1 day'));
  $currentImg = (string)($c['image_url'] ?? 'public/assets/img/collecte-centre.svg');
  $isCustomImg = !array_key_exists($currentImg, CollecteSang::IMAGES_PREDEFINIES);
  $currentGalerie = !empty($c['galerie']) && is_array($c['galerie'])
      ? array_slice($c['galerie'], 0, CollecteSang::MAX_GALERIE_IMAGES)
      : [$currentImg, 'public/assets/img/collecte-mobile.svg', 'public/assets/img/collecte-campus.svg'];
?>
<div class="form-panel collecte-form-panel">
  <span class="eyebrow">ORGANISATION DE COLLECTE (HORLOGE 24H)</span>
  <h2><?= !empty($c['id']) ? 'Modifier la collecte, ses photos et ses horaires' : 'Créer une nouvelle collecte de sang' ?></h2>
  <form method="post" action="index.php?page=collecte-sauver" enctype="multipart/form-data" data-validate="collecte">
    <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
    <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
    <div class="form-grid">
      <div class="field full"><label for="titre">Titre de la collecte</label><input id="titre" name="titre" placeholder="Ex. Journée Don de Sang — Campus Esprit" value="<?= e($_POST['titre'] ?? $c['titre'] ?? '') ?>"></div>
      <div class="field"><label for="ville">Ville</label><input id="ville" name="ville" placeholder="Ex. Tunis" value="<?= e($_POST['ville'] ?? $c['ville'] ?? '') ?>"></div>
      <div class="field"><label for="adresse">Adresse précise</label><input id="adresse" name="adresse" placeholder="Ex. Centre de don, Avenue Habib Bourguiba" value="<?= e($_POST['adresse'] ?? $c['adresse'] ?? '') ?>"></div>
      <div class="field"><label for="debut">Date et heure de début (Format 24h)</label><input id="debut" name="debut" type="datetime-local" value="<?= e($_POST['debut'] ?? (isset($c['debut']) ? date('Y-m-d\TH:i',strtotime($c['debut'])) : $defaultDebut)) ?>"></div>
      <div class="field"><label for="fin">Date et heure de fin (Format 24h — peut s’étendre sur plusieurs jours)</label><input id="fin" name="fin" type="datetime-local" value="<?= e($_POST['fin'] ?? (isset($c['fin']) ? date('Y-m-d\TH:i',strtotime($c['fin'])) : $defaultFin)) ?>"></div>
      <div class="field full"><label for="statut">Statut de publication</label><select id="statut" name="statut"><?php foreach (['BROUILLON'=>'Brouillon (non visible au public)','PUBLIEE'=>'Publiée (ouverte aux donneurs — en cours ou future)','CLOTUREE'=>'Clôturée'] as $s=>$lib): ?><option value="<?= $s ?>" <?= ($_POST['statut'] ?? $c['statut'] ?? 'PUBLIEE')===$s ? 'selected' : '' ?>><?= e($lib) ?></option><?php endforeach; ?></select></div>

      <!-- Aperçus à gauche (1 image pour la carte + jusqu'à 4 images pour la page détail) & Contrôles à droite -->
      <div class="field full cover-upload-box">
        <div class="cover-previews-left">
          <div class="preview-block">
            <span class="preview-block-title">1 · APERÇU CARTE « COLLECTES À VENIR » (1 PHOTO)</span>
            <div class="cover-preview-frame">
              <img id="collecte-cover-preview" src="<?= e($currentImg) ?>" alt="Aperçu de la carte">
              <span>Carte liste des collectes</span>
            </div>
          </div>
          <div class="preview-block">
            <span class="preview-block-title">2 · APERÇU GALERIE PAGE DÉTAIL (MAX <?= CollecteSang::MAX_GALERIE_IMAGES ?> PHOTOS À GAUCHE)</span>
            <div id="collecte-gallery-preview" class="gallery-thumbs-preview">
              <?php foreach ($currentGalerie as $idx => $gPath): ?>
                <div class="gallery-thumb-item">
                  <img src="<?= e($gPath) ?>" alt="Photo détail <?= $idx + 1 ?>">
                  <small>Photo <?= $idx + 1 ?></small>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="cover-upload-controls">
          <span class="eyebrow">GESTION DES PHOTOS DE LA COLLECTE</span>
          <div class="upload-group">
            <label for="image_fichier">1. Photo principale de la carte « Collectes à venir » (1 image)</label>
            <input id="image_fichier" name="image_fichier" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-cover-file="#collecte-cover-preview">
            <label for="image_choix">Ou choisir un thème visuel intégré</label>
            <select id="image_choix" name="image_choix" data-cover-preset="#collecte-cover-preview">
              <?php if ($isCustomImg): ?>
                <option value="CONSERVER" data-src="<?= e($currentImg) ?>" selected>Photo personnalisée actuelle</option>
              <?php endif; ?>
              <?php foreach (CollecteSang::IMAGES_PREDEFINIES as $imgPath => $imgLabel): ?>
                <option value="<?= e($imgPath) ?>" data-src="<?= e($imgPath) ?>" <?= $currentImg === $imgPath ? 'selected' : '' ?>><?= e($imgLabel) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="upload-group">
            <label for="galerie_fichiers">2. Photos de la page détail de la collecte (jusqu’à <?= CollecteSang::MAX_GALERIE_IMAGES ?> images max)</label>
            <input id="galerie_fichiers" name="galerie_fichiers[]" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif" data-gallery-files="#collecte-gallery-preview" data-max-files="<?= CollecteSang::MAX_GALERIE_IMAGES ?>">
            <small class="field-hint">Sélectionnez de 1 à <?= CollecteSang::MAX_GALERIE_IMAGES ?> photos : elles s’afficheront dans la galerie interactive située à gauche sur la page détail de la collecte.</small>
          </div>
        </div>
      </div>
    </div>
    <div class="form-actions"><a class="button button-quiet" href="index.php?page=gestion-collectes">Annuler</a><button class="button button-primary">Enregistrer la collecte</button></div>
  </form>
</div>
<?php elseif ($page==='collecte-gestion' && $detail): require __DIR__.'/collecte-gestion.php'; ?>
<?php elseif ($page==='profil'): require __DIR__.'/../front-office/extra.php'; ?>
<?php elseif (in_array($page,['tableau-bord','gestion-sos','parametres','inviter-responsable','scan-donneur'],true)): require __DIR__.'/extra.php'; ?>
<?php elseif (in_array($page,['admin-annuaire','admin-rendez-vous','admin-pharmacies','admin-laboratoires'],true)): require __DIR__.'/modules.php'; ?>
<?php endif; ?>
