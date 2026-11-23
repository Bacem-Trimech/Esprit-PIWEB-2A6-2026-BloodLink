<?php
$isStaffProfile = ($current && in_array($current['role'] ?? '', ['ADMIN','RESPONSABLE'], true));
?>
<div class="<?= $isStaffProfile ? 'staff-profile-wrap' : 'page-wrap' ?>">
<?php if (!$isStaffProfile): ?>
  <?php foreach ($errors as $err): ?><p class="notice"><?= e($err) ?></p><?php endforeach; ?>
  <?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?>
<?php endif; ?>

<?php if ($page==='profil'): ?>
  <?php if (!$isStaffProfile): ?>
    <div class="page-intro"><div><span class="eyebrow">MON COMPTE DONNEUR</span><h1>Profil et paramètres</h1><p>Complétez vos coordonnées avant d’utiliser les services de votre espace.</p></div></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['bienvenue'])): unset($_SESSION['bienvenue']); ?><div class="notice" role="alert">Bienvenue ! Continuez la configuration de votre compte.</div><?php endif; ?>
  <?php if (!empty($_SESSION['mot_de_passe_change'])): unset($_SESSION['mot_de_passe_change']); ?><div class="notice" role="status">Votre mot de passe a été modifié.</div><?php endif; ?>

  <?php if ($isStaffProfile): ?>
    <div class="profile-balanced-grid">
      <section class="form-panel profile-card">
        <span class="eyebrow">IDENTITÉ DU COMPTE</span>
        <h2>Informations générales</h2>
        <p class="muted">Compte <?= ($current['role']??'')==='ADMIN' ? 'administrateur' : 'responsable de collecte' ?>.</p>
        <form method="post" action="index.php?page=profil-sauver" data-validate="profil">
          <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
          <div class="form-grid">
            <div class="field full">
              <label for="nom">Nom complet</label>
              <input id="nom" name="nom" value="<?= e($_POST['nom'] ?? $profile['nom'] ?? '') ?>">
            </div>
            <div class="field full">
              <label>Adresse e-mail de connexion</label>
              <input value="<?= e($profile['email'] ?? '') ?>" readonly>
            </div>
            <div class="field full">
              <label for="avatar_couleur">Couleur de mon badge</label>
              <select id="avatar_couleur" name="avatar_couleur">
                <?php foreach (['#D92735'=>'Rouge','#3268DC'=>'Bleu','#15966A'=>'Vert','#8B5CF6'=>'Violet','#D97706'=>'Orange'] as $color=>$label): ?>
                  <option value="<?= e($color) ?>" <?= ($_POST['avatar_couleur'] ?? $profile['avatar_couleur'] ?? '#D92735')===$color?'selected':'' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-actions"><button class="button button-primary">Enregistrer mes informations</button></div>
        </form>
      </section>

      <section class="form-panel profile-card">
        <span class="eyebrow">PROTECTION D’ACCÈS</span>
        <h2>Modifier mon mot de passe</h2>
        <p class="muted">Choisissez un mot de passe robuste d’au moins 12 caractères.</p>
        <form method="post" action="index.php?page=mot-de-passe" data-validate="password" class="compact-form">
          <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
          <div class="field"><label for="ancien">Mot de passe actuel</label><input id="ancien" name="ancien" type="password"></div>
          <div class="field"><label for="nouveau">Nouveau mot de passe (12 caractères minimum)</label><input id="nouveau" name="nouveau" type="password"></div>
          <div class="field"><label for="confirmation">Confirmer le nouveau mot de passe</label><input id="confirmation" name="confirmation" type="password"></div>
          <div class="form-actions"><button class="button button-outline">Mettre à jour le mot de passe</button></div>
        </form>
      </section>
    </div>
  <?php else: ?>
    <div class="profile-balanced-grid">
      <section class="form-panel profile-card">
        <span class="eyebrow">FICHE DONNEUR</span>
        <h2>Mes informations personnelles</h2>
        <form method="post" action="index.php?page=profil-sauver" data-validate="profil">
          <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
          <div class="form-grid">
            <div class="field"><label for="nom">Nom complet</label><input id="nom" name="nom" value="<?= e($_POST['nom'] ?? $profile['nom'] ?? '') ?>"></div>
            <div class="field"><label>E-mail</label><input value="<?= e($profile['email'] ?? '') ?>" readonly></div>
            <div class="field"><label for="telephone">Téléphone</label><input id="telephone" name="telephone" value="<?= e($_POST['telephone'] ?? $profile['telephone'] ?? '') ?>"></div>
            <div class="field"><label for="ville">Ville</label><input id="ville" name="ville" value="<?= e($_POST['ville'] ?? $profile['ville'] ?? '') ?>"></div>
            <div class="field"><label for="date_naissance">Date de naissance (18 ans min.)</label><input id="date_naissance" name="date_naissance" type="date" value="<?= e($_POST['date_naissance'] ?? $profile['date_naissance'] ?? '') ?>"></div>
            <div class="field"><label for="groupe_sanguin">Groupe sanguin déclaré (à confirmer au labo)</label><select id="groupe_sanguin" name="groupe_sanguin"><option value="">Je ne sais pas</option><?php foreach (SosController::GROUPES as $g): ?><option <?= ($_POST['groupe_sanguin'] ?? $profile['groupe_sanguin'] ?? '')===$g?'selected':'' ?>><?= e($g) ?></option><?php endforeach; ?></select></div>
            <div class="field full"><label for="avatar_couleur">Couleur de mon avatar</label><select id="avatar_couleur" name="avatar_couleur"><?php foreach (['#D92735'=>'Rouge','#3268DC'=>'Bleu','#15966A'=>'Vert','#8B5CF6'=>'Violet','#D97706'=>'Orange'] as $color=>$label): ?><option value="<?= e($color) ?>" <?= ($_POST['avatar_couleur'] ?? $profile['avatar_couleur'] ?? '#D92735')===$color?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="field full"><label class="checkbox-line"><input type="checkbox" name="preferences_contact" value="1" <?= (int)($profile['preferences_contact']??0)?'checked':'' ?>> J’accepte de recevoir les informations sur les collectes</label></div>
          </div>
          <div class="form-actions"><button class="button button-primary">Enregistrer mon profil</button></div>
        </form>
      </section>
      <section class="form-panel profile-card">
        <span class="eyebrow">CONNEXION & CARTE QR</span>
        <h2>Sécurité du compte</h2>
        <form method="post" action="index.php?page=mot-de-passe" data-validate="password" class="compact-form">
          <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
          <div class="field"><label for="ancien">Mot de passe actuel</label><input id="ancien" name="ancien" type="password"></div>
          <div class="field"><label for="nouveau">Nouveau mot de passe (12 caractères minimum)</label><input id="nouveau" name="nouveau" type="password"></div>
          <div class="field"><label for="confirmation">Confirmer le nouveau mot de passe</label><input id="confirmation" name="confirmation" type="password"></div>
          <div class="form-actions"><button class="button button-outline">Changer le mot de passe</button></div>
        </form>
        <div class="qr-shortcut-box">
          <strong>Carte QR d’identification donneur</strong>
          <p class="muted">Présentez votre QR au responsable lors de votre arrivée sur un lieu de collecte.</p>
          <a class="button button-quiet button-small" href="index.php?page=mon-qr">▣ Afficher ou renouveler mon QR →</a>
        </div>
      </section>
    </div>
  <?php endif; ?>

<?php elseif ($page==='mon-qr'): ?>
<div class="page-intro"><div><span class="eyebrow">IDENTIFICATION</span><h1>Mon QR donneur</h1><p>Présentez ce code au responsable d’une collecte. Il permet de retrouver votre compte après connexion du responsable.</p></div></div><div class="form-panel"><div id="donor-qr" data-code="<?= e($profile['qr_identifiant']) ?>"></div><p class="muted">Code personnel : <code><?= e($profile['qr_identifiant']) ?></code></p><form method="post" action="index.php?page=qr-renouveler" data-confirm="Renouveler le QR ? L’ancien code cessera de fonctionner."><input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>"><button class="button button-outline">Renouveler mon QR</button></form></div>

<?php elseif ($page==='mes-examens'): ?>
<div class="page-intro"><div><span class="eyebrow">SUIVI DE DON</span><h1>Mes examens et résultats</h1><p>Informations saisies par le centre. Pour toute interprétation médicale, contactez le professionnel de santé.</p></div></div><?php foreach ($examens as $x): ?><article class="panel"><h2><?= e($x['titre']) ?> · <?= e(date('d/m/Y',strtotime($x['debut']))) ?></h2><p><strong>Arrivée :</strong> <?= $x['arrivee_le']?e(date('d/m/Y à H:i',strtotime($x['arrivee_le']))):'Non enregistrée' ?> · <strong>Don confirmé :</strong> <?= $x['effectue_le']?e(date('d/m/Y à H:i',strtotime($x['effectue_le']))):'En attente' ?></p><p>Examen avant don : <?= e($x['decision'] ?? 'En attente') ?><?php if ($x['examen_saisi_le']): ?> · enregistré le <?= e(date('d/m/Y à H:i',strtotime($x['examen_saisi_le']))) ?><?php endif; ?></p><?php if ($x['decision']): ?><p>Tension : <?= e((string)($x['tension_systolique']??'—')) ?>/<?= e((string)($x['tension_diastolique']??'—')) ?> mmHg · Pouls : <?= e((string)($x['pouls']??'—')) ?>/min · Température : <?= e((string)($x['temperature_c']??'—')) ?> °C · Hémoglobine : <?= e((string)($x['hemoglobine_g_dl']??'—')) ?> g/dL · Poids : <?= e((string)($x['poids_kg']??'—')) ?> kg</p><?php endif; ?><p>Résultat du centre : <?= e($x['resultat_statut'] ?? 'En attente') ?><?php if ($x['resultat_statut']==='VALIDE' && $x['groupe_confirme']): ?> · Groupe confirmé sain : <span class="blood-chip blood-chip-sain"><strong>✓ <?= e($x['groupe_confirme']) ?></strong></span><?php endif; ?></p><p><strong>Prochain don :</strong> <?php if (($x['statut_prochain_don']??'')==='AUTORISE' && $x['prochain_don_le']): ?>Avis favorable du centre à partir du <?= e(date('d/m/Y',strtotime($x['prochain_don_le']))) ?>. Une nouvelle évaluation reste nécessaire lors du don.<?php elseif (($x['statut_prochain_don']??'')==='NON_AUTORISE'): ?>Non autorisé selon le dernier avis saisi. Contactez le centre.<?php else: ?>Avis en cours : aucune autorisation de nouveau don enregistrée.<?php endif; ?></p><?php if ($x['resultat_statut']==='VALIDE'): ?><p>Dépistages du don : VIH, hépatites B et C, syphilis — non réactifs (aucune infection détectée) selon le laboratoire. Contactez le centre pour l’interprétation et le compte rendu officiel.</p><?php elseif ($x['resultat_statut']==='CONTACT_CENTRE'): ?><p>Veuillez contacter le centre pour recevoir vos résultats de façon confidentielle.</p><?php endif; ?></article><?php endforeach; ?><?php if (!$examens): ?><div class="empty-state">Aucun examen enregistré.</div><?php endif; ?>

<?php elseif ($page==='sos'): ?>
<div class="page-intro">
  <div>
    <span class="eyebrow">URGENCE TRANSFUSIONNELLE (VALIDITÉ 2 HEURES)</span>
    <h1>Alertes SOS sang en cours</h1>
    <p>Appels urgents publiés par les proches ou services hospitaliers. Toute annonce expire automatiquement au bout de 2 heures.</p>
  </div>
  <a class="button button-primary" href="index.php?page=sos-nouveau">+ Publier une alerte SOS</a>
</div>
<div class="sos-grid">
  <?php foreach ($items as $a):
    $allCompat = SosController::compatibles($a['groupe_receveur']);
    $otherCompat = array_values(array_filter($allCompat, static fn(string $g): bool => $g !== $a['groupe_receveur']));
  ?>
  <article class="sos-alert-card">
    <header class="sos-card-top">
      <div class="sos-wanted-badge">
        <span class="sos-drop-icon">♥</span>
        <div>
          <small>GROUPE RECHERCHÉ EN URGENCE</small>
          <strong>RECEVEUR <?= e($a['groupe_receveur']) ?></strong>
        </div>
      </div>
      <span class="sos-countdown-pill sos-countdown" data-expires="<?= e(date(DATE_ATOM,strtotime($a['expire_le']))) ?>">⏱ Calcul…</span>
    </header>

    <div class="sos-card-body">
      <div class="sos-location">
        <span class="sos-city-tag">📍 <?= e($a['ville']) ?></span>
        <h2><?= e($a['lieu']) ?></h2>
      </div>

      <div class="sos-compat-box">
        <div class="sos-compat-row">
          <span class="sos-compat-label">Groupe exact demandé :</span>
          <span class="blood-badge-main">♥ <?= e($a['groupe_receveur']) ?></span>
        </div>
        <div class="sos-compat-row">
          <span class="sos-compat-label">Autres groupes compatibles (globules rouges) :</span>
          <div class="chip-list">
            <?php if (!empty($otherCompat)): ?>
              <?php foreach ($otherCompat as $cg): ?>
                <span class="blood-badge-alt"><?= e($cg) ?></span>
              <?php endforeach; ?>
            <?php else: ?>
              <span class="blood-badge-alt">Uniquement <?= e($a['groupe_receveur']) ?> (donneur universel)</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!empty($a['message'])): ?>
      <div class="sos-message-box">
        <small>Précision de l’annonceur :</small>
        <p><?= e($a['message']) ?></p>
      </div>
      <?php endif; ?>

      <div class="sos-card-footer">
        <a class="button button-primary sos-call-btn" href="tel:<?= e(preg_replace('/[^+0-9]/','',$a['telephone'])) ?>">
          📞 Appeler le <?= e($a['telephone']) ?>
        </a>
        <small class="sos-disclaimer">La compatibilité finale est systématiquement vérifiée par le centre de transfusion.</small>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php if (!$items): ?><div class="empty-state"><h2>Aucune alerte SOS active</h2><p>Toutes les demandes urgentes des deux dernières heures ont expiré ou ont été traitées.</p></div><?php endif; ?>

<?php elseif ($page==='sos-nouveau'): ?>
<div class="form-panel sos-form-panel">
  <div class="sos-form-header">
    <span class="eyebrow">APPEL D’URGENCE · DIFFUSION IMMÉDIATE (2 HEURES)</span>
    <h1>Publier une alerte SOS sang</h1>
    <p>Aucun compte n’est requis. Votre appel et le numéro de contact seront visibles publiquement pendant <strong>2 heures</strong>, avec affichage automatique des groupes sanguins compatibles.</p>
  </div>
  <form method="post" action="index.php?page=sos-creer" data-validate="sos">
    <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
    <div class="form-grid">
      <div class="field">
        <label for="lieu">Établissement / Hôpital / Service précis (liaison Annuaire)</label>
        <input id="lieu" name="lieu" list="annuaire-etabs-list" placeholder="Ex. Hôpital Charles-Nicolle — Urgences" value="<?= e($_POST['lieu']??'') ?>">
        <datalist id="annuaire-etabs-list">
          <?php foreach ($moduleData['etablissements'] ?? [] as $etOpt): ?>
            <option value="<?= e($etOpt['nom'] . ' — ' . $etOpt['ville']) ?>">
          <?php endforeach; ?>
        </datalist>
      </div>
      <div class="field">
        <label for="ville">Ville</label>
        <input id="ville" name="ville" placeholder="Ex. Tunis" value="<?= e($_POST['ville']??'') ?>">
      </div>
      <div class="field">
        <label for="groupe_receveur">Groupe sanguin recherché (Receveur)</label>
        <select id="groupe_receveur" name="groupe_receveur">
          <option value="">— Choisir le groupe du patient —</option>
          <?php foreach(SosController::GROUPES as $g): ?>
            <option value="<?= e($g) ?>" <?= ($_POST['groupe_receveur']??'')===$g?'selected':'' ?>>Groupe <?= e($g) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="telephone">Téléphone de contact direct</label>
        <input id="telephone" name="telephone" placeholder="Ex. +216 98 000 000" value="<?= e($_POST['telephone']??'') ?>">
      </div>
      <div class="field full">
        <label for="message">Précisions utiles (horaires d’accueil, aile/bâtiment — sans identité médicale confidentielle)</label>
        <textarea id="message" name="message" placeholder="Ex. Présentez-vous au centre de transfusion du bloc B avant 16h."><?= e($_POST['message']??'') ?></textarea>
      </div>
    </div>
    <div class="consent-card">
      <label class="checkbox-line">
        <input type="checkbox" name="consentement" value="1">
        <span>J’accepte la publication publique de ce numéro de téléphone pendant <strong>deux heures</strong> pour cette urgence.</span>
      </label>
    </div>
    <div class="form-actions">
      <a class="button button-quiet" href="index.php?page=sos">Annuler</a>
      <button class="button button-primary">♥ Publier l’alerte pendant deux heures</button>
    </div>
  </form>
</div>
<?php endif; ?>
</div>
