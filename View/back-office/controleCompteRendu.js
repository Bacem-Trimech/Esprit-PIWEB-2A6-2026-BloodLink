function verifierCompteRendu() {
    var analyse = document.getElementById("idAnalyse").value;
    var patient = document.getElementById("nomPatient").value.trim();
    var date = document.getElementById("datePrelevement").value;
    var resultat = document.getElementById("resultat").value.trim();
    var interpretation = document.getElementById("interpretation").value;
    var ok = true;

    var spans = ["erreurAnalyse", "erreurPatient", "erreurDate", "erreurResultat", "erreurInterpretation"];
    for (var i = 0; i < spans.length; i++) {
        document.getElementById(spans[i]).innerHTML = "";
    }

    if (analyse == "") {
        document.getElementById("erreurAnalyse").innerHTML = "Choisir l'analyse correspondante";
        ok = false;
    }
    if (patient.length < 3) {
        document.getElementById("erreurPatient").innerHTML = "Le nom du patient doit contenir au moins 3 caractères";
        ok = false;
    }
    if (date == "") {
        document.getElementById("erreurDate").innerHTML = "La date de prélèvement est obligatoire";
        ok = false;
    } else {
        var aujourdhui = new Date().toISOString().slice(0, 10);
        if (date > aujourdhui) {
            document.getElementById("erreurDate").innerHTML = "La date ne peut pas être dans le futur";
            ok = false;
        }
    }
    if (resultat == "") {
        document.getElementById("erreurResultat").innerHTML = "Le résultat est obligatoire";
        ok = false;
    }
    if (interpretation == "") {
        document.getElementById("erreurInterpretation").innerHTML = "Choisir l'interprétation (Normal ou Anormal)";
        ok = false;
    }

    return ok;
}
