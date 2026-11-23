function verifierAnalyse() {
    var nom = document.getElementById("nom").value.trim();
    var description = document.getElementById("description").value.trim();
    var type = document.getElementById("typeEchantillon").value;
    var jeun = document.getElementById("aJeun").value;
    var prix = document.getElementById("prix").value;
    var delai = document.getElementById("delai").value.trim();
    var ok = true;

    var spans = ["erreurNom", "erreurDescription", "erreurType", "erreurJeun", "erreurPrix", "erreurDelai"];
    for (var i = 0; i < spans.length; i++) {
        document.getElementById(spans[i]).innerHTML = "";
    }

    if (nom.length < 3) {
        document.getElementById("erreurNom").innerHTML = "Le nom doit contenir au moins 3 caractères";
        ok = false;
    }
    if (description == "") {
        document.getElementById("erreurDescription").innerHTML = "La description est obligatoire";
        ok = false;
    }
    if (type == "") {
        document.getElementById("erreurType").innerHTML = "Choisir un type d'échantillon";
        ok = false;
    }
    if (jeun == "") {
        document.getElementById("erreurJeun").innerHTML = "Préciser si le patient doit être à jeun";
        ok = false;
    }
    if (prix == "" || isNaN(prix) || prix <= 0) {
        document.getElementById("erreurPrix").innerHTML = "Le prix doit être un nombre positif";
        ok = false;
    }
    if (!/^[0-9]+$/.test(delai) || parseInt(delai) < 1) {
        document.getElementById("erreurDelai").innerHTML = "Le délai doit être un nombre entier d'heures (minimum 1)";
        ok = false;
    }

    return ok;
}
