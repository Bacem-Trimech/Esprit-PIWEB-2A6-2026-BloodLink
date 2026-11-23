<?php
include_once (__DIR__ . '/../config.php');
include_once (__DIR__ . '/../model/analyseMedicale.php');

class AnalyseMedicaleC {

    public function listerAnalyses() {
        $sql = "SELECT * FROM analyse_medicale ORDER BY id";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function recupererAnalyse($id) {
        $sql = "SELECT * FROM analyse_medicale WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function ajouterAnalyse(AnalyseMedicale $analyse) {
        $sql = "INSERT INTO analyse_medicale (nom, description, type_echantillon, prix, delai_heures, a_jeun, date_ajout)
                VALUES (:nom, :description, :type_echantillon, :prix, :delai_heures, :a_jeun, :date_ajout)";
        $db = config::getConnexion();
        $date = $analyse->getDateAjout();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $analyse->getNom(),
                'description' => $analyse->getDescription(),
                'type_echantillon' => $analyse->getTypeEchantillon(),
                'prix' => $analyse->getPrix(),
                'delai_heures' => $analyse->getDelaiHeures(),
                'a_jeun' => $analyse->getAJeun(),
                'date_ajout' => $date->format('Y-m-d')
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function modifierAnalyse(AnalyseMedicale $analyse, $id) {
        $sql = "UPDATE analyse_medicale SET nom = :nom, description = :description, type_echantillon = :type_echantillon,
                prix = :prix, delai_heures = :delai_heures, a_jeun = :a_jeun WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $analyse->getNom(),
                'description' => $analyse->getDescription(),
                'type_echantillon' => $analyse->getTypeEchantillon(),
                'prix' => $analyse->getPrix(),
                'delai_heures' => $analyse->getDelaiHeures(),
                'a_jeun' => $analyse->getAJeun(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function supprimerAnalyse($id) {
        $sql = "DELETE FROM analyse_medicale WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // types d'échantillon autorisés
    public function typesEchantillon() {
        return ['Sang', 'Urine', 'Selles', 'Salive', 'Autre'];
    }

    // controle de saisie
    public function verifierAnalyse($nom, $description, $type, $prix, $delai, $aJeun) {
        $erreurs = [];
        if (strlen(trim($nom)) < 3) {
            $erreurs[] = "Le nom doit contenir au moins 3 caractères";
        }
        if (trim($description) == "") {
            $erreurs[] = "La description est obligatoire";
        }
        if (!in_array($type, $this->typesEchantillon())) {
            $erreurs[] = "Choisir un type d'échantillon";
        }
        if (!is_numeric($prix) || $prix <= 0) {
            $erreurs[] = "Le prix doit être un nombre positif";
        }
        if (!ctype_digit((string)$delai) || (int)$delai < 1) {
            $erreurs[] = "Le délai doit être un nombre entier d'heures (minimum 1)";
        }
        if ($aJeun !== "0" && $aJeun !== "1") {
            $erreurs[] = "Préciser si le patient doit être à jeun";
        }
        return $erreurs;
    }

    // statistiques du dashboard
    public function compterAnalyses() {
        $db = config::getConnexion();
        return (int)$db->query("SELECT COUNT(*) FROM analyse_medicale")->fetchColumn();
    }

    public function compterAJeun() {
        $db = config::getConnexion();
        return (int)$db->query("SELECT COUNT(*) FROM analyse_medicale WHERE a_jeun = 1")->fetchColumn();
    }

    public function compterSansCompteRendu() {
        $db = config::getConnexion();
        return (int)$db->query("SELECT COUNT(*) FROM analyse_medicale a
                                WHERE NOT EXISTS (SELECT 1 FROM compte_rendu_analyse c WHERE c.id_analyse = a.id)")->fetchColumn();
    }

    // nombre de comptes rendus et de résultats anormaux par analyse
    public function statistiquesParAnalyse() {
        $sql = "SELECT a.nom, a.type_echantillon,
                       COUNT(c.id) AS total,
                       COALESCE(SUM(c.interpretation = 'Anormal'), 0) AS anormaux
                FROM analyse_medicale a
                LEFT JOIN compte_rendu_analyse c ON c.id_analyse = a.id
                GROUP BY a.id, a.nom, a.type_echantillon
                ORDER BY total DESC, a.nom";
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
