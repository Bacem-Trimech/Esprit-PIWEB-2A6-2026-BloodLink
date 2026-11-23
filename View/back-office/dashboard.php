<?php
include_once '../../controller/analyseMedicaleC.php';
include_once '../../controller/compteRenduAnalyseC.php';

$analyseC = new AnalyseMedicaleC();
$crC = new CompteRenduAnalyseC();

$totalAnalyses = $analyseC->compterAnalyses();
$aJeun = $analyseC->compterAJeun();
$sansCompteRendu = $analyseC->compterSansCompteRendu();
$totalCR = $crC->compterComptesRendus();
$anormaux = $crC->compterAnormaux();
$aujourdhui = $crC->compterAujourdhui();
$stats = $analyseC->statistiquesParAnalyse();

$pageTitle = 'Dashboard';
$active = 'dashboard';
include '../partials/header.php';
?>
<h1 class="page-title">Overview</h1>
<p class="subtitle">Bienvenue dans le module Laboratoires : analyses médicales et comptes rendus.</p>

<div class="grid-3">
    <div class="card stat">
        <div>
            <div class="label">Total analyses</div>
            <div class="value"><?php echo $totalAnalyses; ?></div>
            <div class="hint">Dans le catalogue</div>
        </div>
        <div class="stat-icon"><?php echo icon('flask'); ?></div>
    </div>
    <div class="card stat">
        <div>
            <div class="label">Comptes rendus</div>
            <div class="value"><?php echo $totalCR; ?></div>
            <div class="hint">Total : <?php echo $totalCR; ?></div>
        </div>
        <div class="stat-icon blue"><?php echo icon('report'); ?></div>
    </div>
    <div class="card stat">
        <div>
            <div class="label">Résultats anormaux</div>
            <div class="value"><?php echo $anormaux; ?></div>
            <div class="hint">Attention requise</div>
        </div>
        <div class="stat-icon amber"><?php echo icon('alert'); ?></div>
    </div>
    <div class="card stat">
        <div>
            <div class="label">Créés aujourd'hui</div>
            <div class="value"><?php echo $aujourdhui; ?></div>
            <div class="hint">Comptes rendus du jour</div>
        </div>
        <div class="stat-icon green"><?php echo icon('check'); ?></div>
    </div>
    <div class="card stat">
        <div>
            <div class="label">Analyses à jeun</div>
            <div class="value"><?php echo $aJeun; ?></div>
            <div class="hint">Total : <?php echo $totalAnalyses; ?></div>
        </div>
        <div class="stat-icon purple"><?php echo icon('calendar'); ?></div>
    </div>
    <div class="card stat">
        <div>
            <div class="label">Sans compte rendu</div>
            <div class="value"><?php echo $sansCompteRendu; ?></div>
            <div class="hint">Analyses jamais réalisées</div>
        </div>
        <div class="stat-icon"><?php echo icon('file'); ?></div>
    </div>
</div>

<h2 class="section-title">Comptes rendus par analyse</h2>
<?php if (count($stats) == 0) { ?>
    <div class="card empty">Aucune analyse pour le moment. <a href="addAnalyse.php" style="color:var(--red);font-weight:600">Ajouter une analyse</a></div>
<?php } else { ?>
<div class="grid-inv">
    <?php foreach ($stats as $s) { ?>
    <div class="inv">
        <div class="name"><?php echo htmlspecialchars($s['nom']); ?></div>
        <div class="dose"><?php echo htmlspecialchars($s['type_echantillon']); ?></div>
        <div class="units"><?php echo (int)$s['total']; ?> comptes rendus</div>
        <?php if ($s['anormaux'] > 0) { ?>
            <div class="flag low"><?php echo icon('alert'); ?> <?php echo (int)$s['anormaux']; ?> anormal(aux)</div>
        <?php } else { ?>
            <div class="flag ok"><?php echo icon('check'); ?> Aucun anormal</div>
        <?php } ?>
    </div>
    <?php } ?>
</div>
<?php } ?>
<?php include '../partials/footer.php'; ?>
