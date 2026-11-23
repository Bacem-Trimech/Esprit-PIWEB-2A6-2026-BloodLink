<?php
include_once '../../controller/compteRenduAnalyseC.php';

$crC = new CompteRenduAnalyseC();
$c = isset($_GET['id']) ? $crC->afficherCompteRendu($_GET['id']) : false;

$pageTitle = 'Compte rendu d\'analyse';
$active = 'consulter';
include '../partials/header.php';
?>
<a class="btn light" href="consulterCompteRendu.php" style="margin-bottom:18px"><?php echo icon('search'); ?> Retour à la recherche</a>

<?php if (!$c) { ?>
    <div class="card empty">Compte rendu introuvable.</div>
<?php } else { ?>
<div class="card report">
    <div class="report-head">
        <div>
            <div class="name"><?php echo htmlspecialchars($c['nomAnalyse']); ?></div>
            <div class="result-meta"><?php echo htmlspecialchars($c['descriptionAnalyse']); ?></div>
        </div>
        <?php if ($c['interpretation'] == 'Anormal') { ?>
            <span class="badge red">Anormal</span>
        <?php } else { ?>
            <span class="badge green">Normal</span>
        <?php } ?>
    </div>

    <div class="detail-grid">
        <div><div class="k">Patient</div><div class="v"><?php echo htmlspecialchars($c['nom_patient']); ?></div></div>
        <div><div class="k">Date de prélèvement</div><div class="v"><?php echo $c['date_prelevement']; ?></div></div>
        <div><div class="k">Type d'échantillon</div><div class="v"><?php echo htmlspecialchars($c['type_echantillon']); ?></div></div>
        <div><div class="k">Compte rendu établi le</div><div class="v"><?php echo $c['date_creation']; ?></div></div>
    </div>

    <div class="k result-meta" style="font-weight:600;margin-bottom:6px">Résultat</div>
    <div class="result-box"><?php echo htmlspecialchars($c['resultat']); ?></div>

    <?php if (!empty($c['commentaire'])) { ?>
        <div class="k result-meta" style="font-weight:600;margin:16px 0 6px">Commentaire</div>
        <div><?php echo htmlspecialchars($c['commentaire']); ?></div>
    <?php } ?>
</div>
<?php } ?>
<?php include '../partials/footer.php'; ?>
