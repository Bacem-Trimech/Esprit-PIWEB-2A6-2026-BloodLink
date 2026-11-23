<?php
include_once '../../controller/compteRenduAnalyseC.php';
include_once '../../controller/analyseMedicaleC.php';

$crC = new CompteRenduAnalyseC();
$analyseC = new AnalyseMedicaleC();
$analyses = $analyseC->listerAnalyses();
$erreurs = [];

if (isset($_POST['nomPatient'])) {
    $idAnalyse = $_POST['idAnalyse'];
    $nomPatient = $_POST['nomPatient'];
    $datePrelevement = $_POST['datePrelevement'];
    $resultat = $_POST['resultat'];
    $interpretation = $_POST['interpretation'];
    $commentaire = $_POST['commentaire'];

    $erreurs = $crC->verifierCompteRendu($idAnalyse, $nomPatient, $datePrelevement, $resultat, $interpretation);

    if (count($erreurs) == 0) {
        $cr = new CompteRenduAnalyse(null, (int)$idAnalyse, $nomPatient, new DateTime($datePrelevement), $resultat, $interpretation, $commentaire, new DateTime());
        $crC->ajouterCompteRendu($cr);
        header('Location: compteRenduList.php');
        exit;
    }
}

$pageTitle = 'Ajouter un compte rendu';
$active = 'comptesrendus';
include '../partials/header.php';
?>
<h1 class="page-title">Ajouter un compte rendu</h1>
<p class="subtitle">Saisissez le résultat d'une analyse pour un patient.</p>

<div class="card form-card">
    <?php if (count($erreurs) > 0) { ?>
        <div class="alert"><ul>
            <?php foreach ($erreurs as $erreur) { ?><li><?php echo $erreur; ?></li><?php } ?>
        </ul></div>
    <?php } ?>

    <form method="POST" onsubmit="return verifierCompteRendu()">
        <div class="field">
            <label for="idAnalyse">Analyse correspondante</label>
            <select id="idAnalyse" name="idAnalyse">
                <option value="">-- Choisir --</option>
                <?php foreach ($analyses as $a) { ?>
                    <option value="<?php echo $a['id']; ?>" <?php if (($_POST['idAnalyse'] ?? '') == $a['id']) { echo 'selected'; } ?>><?php echo htmlspecialchars($a['nom']); ?></option>
                <?php } ?>
            </select>
            <span class="err" id="erreurAnalyse"></span>
        </div>
        <div class="row-2">
            <div class="field">
                <label for="nomPatient">Nom du patient</label>
                <input type="text" id="nomPatient" name="nomPatient" value="<?php echo htmlspecialchars($_POST['nomPatient'] ?? ''); ?>">
                <span class="err" id="erreurPatient"></span>
            </div>
            <div class="field">
                <label for="datePrelevement">Date de prélèvement</label>
                <input type="date" id="datePrelevement" name="datePrelevement" value="<?php echo htmlspecialchars($_POST['datePrelevement'] ?? ''); ?>">
                <span class="err" id="erreurDate"></span>
            </div>
        </div>
        <div class="field">
            <label for="resultat">Résultat</label>
            <textarea id="resultat" name="resultat"><?php echo htmlspecialchars($_POST['resultat'] ?? ''); ?></textarea>
            <span class="err" id="erreurResultat"></span>
        </div>
        <div class="field">
            <label for="interpretation">Interprétation</label>
            <select id="interpretation" name="interpretation">
                <option value="">-- Choisir --</option>
                <option value="Normal" <?php if (($_POST['interpretation'] ?? '') === 'Normal') { echo 'selected'; } ?>>Normal</option>
                <option value="Anormal" <?php if (($_POST['interpretation'] ?? '') === 'Anormal') { echo 'selected'; } ?>>Anormal</option>
            </select>
            <span class="err" id="erreurInterpretation"></span>
        </div>
        <div class="field">
            <label for="commentaire">Commentaire (facultatif)</label>
            <textarea id="commentaire" name="commentaire"><?php echo htmlspecialchars($_POST['commentaire'] ?? ''); ?></textarea>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit">Ajouter</button>
            <a class="btn light" href="compteRenduList.php">Annuler</a>
        </div>
    </form>
</div>
<script src="controleCompteRendu.js"></script>
<?php include '../partials/footer.php'; ?>
