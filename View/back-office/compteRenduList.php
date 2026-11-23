<?php
include_once '../../controller/compteRenduAnalyseC.php';
$crC = new CompteRenduAnalyseC();
$liste = $crC->listerComptesRendus();

$pageTitle = 'Comptes rendus';
$active = 'comptesrendus';
include '../partials/header.php';
?>
<div class="toolbar">
    <div>
        <h1 class="page-title">Comptes rendus</h1>
        <p class="subtitle" style="margin:0">Comptes rendus d'analyses avec le nom de l'analyse correspondante.</p>
    </div>
    <a class="btn" href="addCompteRendu.php"><?php echo icon('plus'); ?> Ajouter un compte rendu</a>
</div>

<div class="card table-wrap">
    <table class="table">
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Analyse</th>
            <th>Prélèvement</th>
            <th>Interprétation</th>
            <th>Actions</th>
        </tr>
        <?php $vide = true; foreach ($liste as $c) { $vide = false; ?>
        <tr>
            <td><?php echo $c['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($c['nom_patient']); ?></strong></td>
            <td><?php echo htmlspecialchars($c['nomAnalyse']); ?></td>
            <td><?php echo $c['date_prelevement']; ?></td>
            <td>
                <?php if ($c['interpretation'] == 'Anormal') { ?>
                    <span class="badge red">Anormal</span>
                <?php } else { ?>
                    <span class="badge green">Normal</span>
                <?php } ?>
            </td>
            <td>
                <div class="actions">
                    <a class="btn-icon" title="Afficher" href="../frontoffice/voirCompteRendu.php?id=<?php echo $c['id']; ?>"><?php echo icon('eye'); ?></a>
                    <a class="btn-icon" title="Modifier" href="updateCompteRendu.php?id=<?php echo $c['id']; ?>"><?php echo icon('edit'); ?></a>
                    <a class="btn-icon" title="Supprimer" href="deleteCompteRendu.php?id=<?php echo $c['id']; ?>" onclick="return confirm('Supprimer ce compte rendu ?')"><?php echo icon('trash'); ?></a>
                </div>
            </td>
        </tr>
        <?php } ?>
        <?php if ($vide) { ?>
        <tr><td colspan="6" class="empty">Aucun compte rendu. Ajoutez le premier.</td></tr>
        <?php } ?>
    </table>
</div>
<?php include '../partials/footer.php'; ?>
