<?php
include_once '../../controller/analyseMedicaleC.php';
$analyseC = new AnalyseMedicaleC();
$liste = $analyseC->listerAnalyses();

$pageTitle = 'Analyses médicales';
$active = 'analyses';
include '../partials/header.php';
?>
<div class="toolbar">
    <div>
        <h1 class="page-title">Analyses médicales</h1>
        <p class="subtitle" style="margin:0">Catalogue des analyses proposées par le laboratoire.</p>
    </div>
    <a class="btn" href="addAnalyse.php"><?php echo icon('plus'); ?> Ajouter une analyse</a>
</div>

<div class="card table-wrap">
    <table class="table">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Échantillon</th>
            <th>Prix (DT)</th>
            <th>Délai</th>
            <th>À jeun</th>
            <th>Date ajout</th>
            <th>Actions</th>
        </tr>
        <?php $vide = true; foreach ($liste as $a) { $vide = false; ?>
        <tr>
            <td><?php echo $a['id']; ?></td>
            <td>
                <strong><?php echo htmlspecialchars($a['nom']); ?></strong><br>
                <span class="result-meta"><?php echo htmlspecialchars($a['description']); ?></span>
            </td>
            <td><?php echo htmlspecialchars($a['type_echantillon']); ?></td>
            <td><?php echo $a['prix']; ?></td>
            <td><?php echo $a['delai_heures']; ?> h</td>
            <td>
                <?php if ($a['a_jeun']) { ?>
                    <span class="badge red">Oui</span>
                <?php } else { ?>
                    <span class="badge green">Non</span>
                <?php } ?>
            </td>
            <td><?php echo $a['date_ajout']; ?></td>
            <td>
                <div class="actions">
                    <a class="btn-icon" title="Modifier" href="updateAnalyse.php?id=<?php echo $a['id']; ?>"><?php echo icon('edit'); ?></a>
                    <a class="btn-icon" title="Supprimer" href="deleteAnalyse.php?id=<?php echo $a['id']; ?>" onclick="return confirm('Supprimer cette analyse et ses comptes rendus ?')"><?php echo icon('trash'); ?></a>
                </div>
            </td>
        </tr>
        <?php } ?>
        <?php if ($vide) { ?>
        <tr><td colspan="8" class="empty">Aucune analyse. Ajoutez la première.</td></tr>
        <?php } ?>
    </table>
</div>
<?php include '../partials/footer.php'; ?>
