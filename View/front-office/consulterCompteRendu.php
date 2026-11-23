<?php
include_once '../../controller/compteRenduAnalyseC.php';

$crC = new CompteRenduAnalyseC();
$erreurs = [];
$resultats = null;
$patient = $_GET['patient'] ?? "";

if (isset($_GET['patient'])) {
    if (strlen(trim($patient)) < 2) {
        $erreurs[] = "Saisir au moins 2 caractères du nom du patient";
    } else {
        $resultats = $crC->rechercherParPatient(trim($patient));
    }
}

$pageTitle = 'Consulter un compte rendu';
$active = 'consulter';
include '../partials/header.php';
?>
<h1 class="page-title">Consulter un compte rendu</h1>
<p class="subtitle">Recherchez un patient pour afficher ses comptes rendus avec le nom de l'analyse.</p>

<div class="card">
    <?php if (count($erreurs) > 0) { ?>
        <div class="alert"><ul>
            <?php foreach ($erreurs as $erreur) { ?><li><?php echo $erreur; ?></li><?php } ?>
        </ul></div>
    <?php } ?>

    <form method="GET" class="search-form" style="grid-template-columns: 1fr auto" onsubmit="return verifierRecherche()">
        <div class="field">
            <label for="patient">Nom du patient</label>
            <input type="text" id="patient" name="patient" value="<?php echo htmlspecialchars($patient); ?>">
            <span class="err" id="erreurPatient"></span>
        </div>
        <button class="btn" type="submit"><?php echo icon('search'); ?> Rechercher</button>
    </form>
</div>

<?php if ($resultats !== null) { ?>
    <h2 class="section-title">Résultats</h2>
    <?php if (count($resultats) == 0) { ?>
        <div class="card empty">Aucun compte rendu trouvé pour ce patient.</div>
    <?php } else { ?>
        <div class="card table-wrap">
            <table class="table">
                <tr>
                    <th>Patient</th>
                    <th>Analyse</th>
                    <th>Prélèvement</th>
                    <th>Interprétation</th>
                    <th>Compte rendu</th>
                </tr>
                <?php foreach ($resultats as $r) { ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['nom_patient']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['nomAnalyse']); ?></td>
                    <td><?php echo $r['date_prelevement']; ?></td>
                    <td>
                        <?php if ($r['interpretation'] == 'Anormal') { ?>
                            <span class="badge red">Anormal</span>
                        <?php } else { ?>
                            <span class="badge green">Normal</span>
                        <?php } ?>
                    </td>
                    <td><a class="btn light" href="voirCompteRendu.php?id=<?php echo $r['id']; ?>"><?php echo icon('eye'); ?> Afficher</a></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
<?php } ?>

<script>
function verifierRecherche() {
    document.getElementById("erreurPatient").innerHTML = "";
    if (document.getElementById("patient").value.trim().length < 2) {
        document.getElementById("erreurPatient").innerHTML = "Saisir au moins 2 caractères du nom du patient";
        return false;
    }
    return true;
}
</script>
<?php include '../partials/footer.php'; ?>
