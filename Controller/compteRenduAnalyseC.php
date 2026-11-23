<?php
include_once (__DIR__ . '/../config.php');
include_once (__DIR__ . '/../model/compteRenduAnalyse.php');

class CompteRenduAnalyseC {

    // liste des comptes rendus avec le nom de l'analyse correspondante
    public function listerComptesRendus() {
        $sql = "SELECT c.*, a.nom AS nomAnalyse
                FROM compte_rendu_analyse c
                JOIN analyse_medicale a ON c.id_analyse = a.id
                ORDER BY c.date_prelevement DESC, c.id DESC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function recupererCompteRendu($id) {
        $sql = "SELECT * FROM compte_rendu_analyse WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Fonctionnalité principale :
    // afficher le compte rendu avec le nom de l'analyse correspondante
    // (CompteRenduAnalyse + AnalyseMedicale)
    public function afficherCompteRendu($id) {
        $sql = "SELECT c.*, a.nom AS nomAnalyse, a.type_echantillon, a.description AS descriptionAnalyse
                FROM compte_rendu_analyse c
                JOIN analyse_medicale a ON c.id_analyse = a.id
                WHERE c.id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // recherche par nom de patient (avec le nom de l'analyse)
    public function rechercherParPatient($nomPatient) {
        $sql = "SELECT c.id, c.nom_patient, c.date_prelevement, c.interpretation, a.nom AS nomAnalyse
                FROM compte_rendu_analyse c
                JOIN analyse_medicale a ON c.id_analyse = a.id
                WHERE c.nom_patient LIKE :nom
                ORDER BY c.date_prelevement DESC, c.id DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['nom' => '%' . $nomPatient . '%']);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function ajouterCompteRendu(CompteRenduAnalyse $cr) {
        $sql = "INSERT INTO compte_rendu_analyse (id_analyse, nom_patient, date_prelevement, resultat, interpretation, commentaire, date_creation)
                VALUES (:id_analyse, :nom_patient, :date_prelevement, :resultat, :interpretation, :commentaire, :date_creation)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_analyse' => $cr->getIdAnalyse(),
                'nom_patient' => $cr->getNomPatient(),
                'date_prelevement' => $cr->getDatePrelevement()->format('Y-m-d'),
                'resultat' => $cr->getResultat(),
                'interpretation' => $cr->getInterpretation(),
                'commentaire' => $cr->getCommentaire(),
                'date_creation' => $cr->getDateCreation()->format('Y-m-d')
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function modifierCompteRendu(CompteRenduAnalyse $cr, $id) {
        $sql = "UPDATE compte_rendu_analyse SET id_analyse = :id_analyse, nom_patient = :nom_patient,
                date_prelevement = :date_prelevement, resultat = :resultat, interpretation = :interpretation,
                commentaire = :commentaire WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_analyse' => $cr->getIdAnalyse(),
                'nom_patient' => $cr->getNomPatient(),
                'date_prelevement' => $cr->getDatePrelevement()->format('Y-m-d'),
                'resultat' => $cr->getResultat(),
                'interpretation' => $cr->getInterpretation(),
                'commentaire' => $cr->getCommentaire(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function supprimerCompteRendu($id) {
        $sql = "DELETE FROM compte_rendu_analyse WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // controle de saisie
    public function verifierCompteRendu($idAnalyse, $nomPatient, $datePrelevement, $resultat, $interpretation) {
        $erreurs = [];
        if ($idAnalyse == "") {
            $erreurs[] = "Choisir l'analyse correspondante";
        }
        if (strlen(trim($nomPatient)) < 3) {
            $erreurs[] = "Le nom du patient doit contenir au moins 3 caractères";
        }
        $d = DateTime::createFromFormat('Y-m-d', $datePrelevement);
        if (!$d) {
            $erreurs[] = "La date de prélèvement est invalide";
        } elseif ($d > new DateTime('today')) {
            $erreurs[] = "La date de prélèvement ne peut pas être dans le futur";
        }
        if (trim($resultat) == "") {
            $erreurs[] = "Le résultat est obligatoire";
        }
        if ($interpretation !== "Normal" && $interpretation !== "Anormal") {
            $erreurs[] = "Choisir l'interprétation (Normal ou Anormal)";
        }
        return $erreurs;
    }

    // statistiques du dashboard
    public function compterComptesRendus() {
        $db = config::getConnexion();
        return (int)$db->query("SELECT COUNT(*) FROM compte_rendu_analyse")->fetchColumn();
    }

    public function compterAnormaux() {
        $db = config::getConnexion();
        return (int)$db->query("SELECT COUNT(*) FROM compte_rendu_analyse WHERE interpretation = 'Anormal'")->fetchColumn();
    }

    public function compterAujourdhui() {
        $db = config::getConnexion();
        return (int)$db->query("SELECT COUNT(*) FROM compte_rendu_analyse WHERE date_creation = CURDATE()")->fetchColumn();
    }
}
