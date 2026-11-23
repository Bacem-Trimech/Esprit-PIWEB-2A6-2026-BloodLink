<?php
include_once '../../controller/analyseMedicaleC.php';

$analyseC = new AnalyseMedicaleC();
$erreurs = [];

$id = $_GET['id'];
$a = $analyseC->recupererAnalyse($id);

if (isset($_POST['nom'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $type = $_POST['typeEchantillon'];
    $prix = $_POST['prix'];
    $delai = $_POST['delai'];
    $aJeun = $_POST['aJeun'] ?? "";

    $erreurs = $analyseC->verifierAnalyse($nom, $description, $type, $prix, $delai, $aJeun);

    if (count($erreurs) == 0) {
        $analyse = new AnalyseMedicale($id, $nom, $description, $type, (float)$prix, (int)$delai, (int)$aJeun, null);
        $analyseC->modifierAnalyse($analyse, $id);
        header('Location: analyseList.php');
        exit;
    }
}

$pageTitle = 'Modifier une analyse';
$active = 'analyses';
include '../partials/header.php';
?>
<h1 class="page-title">Modifier une analyse</h1>
<p class="subtitle">Mettez à jour les informations de l'analyse.</p>

<div class="card form-card">
    <?php if (count($erreurs) > 0) { ?>
        <div class="alert"><ul>
            <?php foreach ($erreurs as $erreur) { ?><li><?php echo $erreur; ?></li><?php } ?>
        </ul></div>
    <?php } ?>

    <form method="POST" onsubmit="return verifierAnalyse()">
        <div class="field">
            <label for="nom">Nom de l'analyse</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($a['nom']); ?>">
            <span class="err" id="erreurNom"></span>
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($a['description']); ?></textarea>
            <span class="err" id="erreurDescription"></span>
        </div>
        <div class="row-2">
            <div class="field">
                <label for="typeEchantillon">Type d'échantillon</label>
                <select id="typeEchantillon" name="typeEchantillon">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($analyseC->typesEchantillon() as $t) { ?>
                        <option value="<?php echo $t; ?>" <?php if ($a['type_echantillon'] == $t) { echo 'selected'; } ?>><?php echo $t; ?></option>
                    <?php } ?>
                </select>
                <span class="err" id="erreurType"></span>
            </div>
            <div class="field">
                <label for="aJeun">Patient à jeun</label>
                <select id="aJeun" name="aJeun">
                    <option value="">-- Choisir --</option>
                    <option value="0" <?php if ((string)$a['a_jeun'] === '0') { echo 'selected'; } ?>>Non</option>
                    <option value="1" <?php if ((string)$a['a_jeun'] === '1') { echo 'selected'; } ?>>Oui</option>
                </select>
                <span class="err" id="erreurJeun"></span>
            </div>
        </div>
        <div class="row-2">
            <div class="field">
                <label for="prix">Prix (DT)</label>
                <input type="text" id="prix" name="prix" value="<?php echo htmlspecialchars($a['prix']); ?>">
                <span class="err" id="erreurPrix"></span>
            </div>
            <div class="field">
                <label for="delai">Délai du résultat (heures)</label>
                <input type="text" id="delai" name="delai" value="<?php echo htmlspecialchars($a['delai_heures']); ?>">
                <span class="err" id="erreurDelai"></span>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn light" href="analyseList.php">Annuler</a>
        </div>
    </form>
</div>
<script src="controleAnalyse.js"></script>
<?php include '../partials/footer.php'; ?>
