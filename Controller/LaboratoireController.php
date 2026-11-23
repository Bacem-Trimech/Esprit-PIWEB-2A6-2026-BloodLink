<?php
declare(strict_types=1);

final class LaboratoireController
{
    public function listerAnalyses(?string $type = null, ?int $etabId = null): array
    {
        $sql = 'SELECT a.*, e.nom AS laboratoire_nom, e.ville AS laboratoire_ville,
                       (SELECT COUNT(*) FROM compte_rendu_analyse c WHERE c.id_analyse = a.id) AS nb_comptes_rendus
                FROM analyse_medicale a
                LEFT JOIN etablissements_sante e ON e.id = a.etablissement_id
                WHERE 1=1';
        $params = [];
        if ($type !== null && $type !== '' && in_array($type, AnalyseMedicale::TYPES_ECHANTILLON, true)) {
            $sql .= ' AND a.type_echantillon = :type';
            $params['type'] = $type;
        }
        if ($etabId !== null && $etabId > 0) {
            $sql .= ' AND a.etablissement_id = :etab';
            $params['etab'] = $etabId;
        }
        $sql .= ' ORDER BY a.nom ASC';
        $stmt = connexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function laboratoires(): array
    {
        return connexion()->query("SELECT * FROM etablissements_sante WHERE type = 'laboratoire' ORDER BY nom ASC")->fetchAll();
    }

    public function listerPatientsPourLabo(): array
    {
        exigerRole(['ADMIN','RESPONSABLE_LABO']);
        return connexion()->query("SELECT id, nom, email FROM utilisateurs WHERE actif = 1 ORDER BY nom ASC")->fetchAll();
    }

    public function listerComptesRendus(?int $utilisateurId = null, ?string $recherchePatient = null): array
    {
        $sql = 'SELECT c.*, a.nom AS analyse_nom, a.type_echantillon, a.prix,
                       e.nom AS laboratoire_nom, e.ville AS laboratoire_ville,
                       u.nom AS compte_nom, u.email AS compte_email
                FROM compte_rendu_analyse c
                JOIN analyse_medicale a ON a.id = c.id_analyse
                LEFT JOIN etablissements_sante e ON e.id = a.etablissement_id
                LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
                WHERE 1=1';
        $params = [];
        if ($utilisateurId !== null) {
            $sql .= ' AND c.utilisateur_id = :uid';
            $params['uid'] = $utilisateurId;
        }
        if ($recherchePatient !== null && trim($recherchePatient) !== '') {
            $sql .= ' AND c.nom_patient LIKE :pat';
            $params['pat'] = '%' . trim($recherchePatient) . '%';
        }
        $sql .= ' ORDER BY c.date_prelevement DESC, c.id DESC';
        $stmt = connexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function sauverAnalyse(array $post, ?int $id = null): array
    {
        exigerRole(['ADMIN','RESPONSABLE_LABO']);
        verifierCsrf();

        $nom = trim((string)($post['nom'] ?? ''));
        $description = trim((string)($post['description'] ?? ''));
        $type = trim((string)($post['type_echantillon'] ?? 'Sang'));
        $prixRaw = str_replace(',', '.', trim((string)($post['prix'] ?? '')));
        $delai = (int)($post['delai_heures'] ?? 0);
        $aJeun = !empty($post['a_jeun']);
        $etabId = (int)($post['etablissement_id'] ?? 0);

        $errors = [];
        if (mb_strlen($nom) < 3 || mb_strlen($nom) > 120) $errors[] = 'Le nom de l’analyse doit contenir entre 3 et 120 caractères.';
        if (mb_strlen($description) < 4 || mb_strlen($description) > 255) $errors[] = 'Description invalide (4 à 255 caractères).';
        if (!in_array($type, AnalyseMedicale::TYPES_ECHANTILLON, true)) $errors[] = 'Type d’échantillon invalide.';
        if (!is_numeric($prixRaw) || (float)$prixRaw <= 0) $errors[] = 'Le prix doit être un montant positif.';
        if ($delai < 1 || $delai > 720) $errors[] = 'Le délai doit être compris entre 1 et 720 heures.';
        if ($errors) return $errors;

        $analyse = new AnalyseMedicale($id, $nom, $description, $type, (float)$prixRaw, $delai, $aJeun, $etabId > 0 ? $etabId : null, date('Y-m-d'));

        if ($id) {
            $stmt = connexion()->prepare('UPDATE analyse_medicale SET nom=?, description=?, type_echantillon=?, prix=?, delai_heures=?, a_jeun=?, etablissement_id=? WHERE id=?');
            $stmt->execute([$analyse->getNom(), $analyse->getDescription(), $analyse->getTypeEchantillon(), $analyse->getPrix(), $analyse->getDelaiHeures(), $analyse->isAJeun() ? 1 : 0, $analyse->getEtablissementId(), $id]);
        } else {
            $stmt = connexion()->prepare('INSERT INTO analyse_medicale (nom, description, type_echantillon, prix, delai_heures, a_jeun, etablissement_id, date_ajout) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$analyse->getNom(), $analyse->getDescription(), $analyse->getTypeEchantillon(), $analyse->getPrix(), $analyse->getDelaiHeures(), $analyse->isAJeun() ? 1 : 0, $analyse->getEtablissementId(), $analyse->getDateAjout()]);
        }
        return [];
    }

    public function supprimerAnalyse(int $id): void
    {
        exigerRole(['ADMIN','RESPONSABLE_LABO']);
        verifierCsrf();
        connexion()->prepare('DELETE FROM analyse_medicale WHERE id = ?')->execute([$id]);
    }

    public function sauverCompteRendu(array $post, ?int $id = null): array
    {
        exigerRole(['ADMIN','RESPONSABLE_LABO']);
        verifierCsrf();

        $idAnalyse = (int)($post['id_analyse'] ?? 0);
        $utilisateurId = (int)($post['utilisateur_id'] ?? 0);
        $nomPatient = trim((string)($post['nom_patient'] ?? ''));
        $datePrel = trim((string)($post['date_prelevement'] ?? ''));
        $resultat = trim((string)($post['resultat'] ?? ''));
        $interpretation = trim((string)($post['interpretation'] ?? 'Normal'));
        $commentaire = trim((string)($post['commentaire'] ?? ''));

        if ($utilisateurId > 0 && $nomPatient === '') {
            $q = connexion()->prepare('SELECT nom FROM utilisateurs WHERE id = ?');
            $q->execute([$utilisateurId]);
            $nomPatient = (string)($q->fetchColumn() ?: '');
        }

        $errors = [];
        if ($idAnalyse <= 0) $errors[] = 'Choisissez une analyse médicale.';
        if (mb_strlen($nomPatient) < 3 || mb_strlen($nomPatient) > 120) $errors[] = 'Le nom du patient doit contenir au moins 3 caractères.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePrel) || $datePrel > date('Y-m-d')) $errors[] = 'Date de prélèvement invalide (ne peut pas être dans le futur).';
        if (mb_strlen($resultat) < 3) $errors[] = 'Le résultat de l’analyse est obligatoire.';
        if (!in_array($interpretation, CompteRenduAnalyse::INTERPRETATIONS, true)) $errors[] = 'Interprétation invalide (Normal ou Anormal).';
        if ($errors) return $errors;

        $cr = new CompteRenduAnalyse($id, $idAnalyse, $utilisateurId > 0 ? $utilisateurId : null, $nomPatient, $datePrel, $resultat, $interpretation, $commentaire !== '' ? $commentaire : null, date('Y-m-d'));

        if ($id) {
            $stmt = connexion()->prepare('UPDATE compte_rendu_analyse SET id_analyse=?, utilisateur_id=?, nom_patient=?, date_prelevement=?, resultat=?, interpretation=?, commentaire=? WHERE id=?');
            $stmt->execute([$cr->getIdAnalyse(), $cr->getUtilisateurId(), $cr->getNomPatient(), $cr->getDatePrelevement(), $cr->getResultat(), $cr->getInterpretation(), $cr->getCommentaire(), $id]);
        } else {
            $stmt = connexion()->prepare('INSERT INTO compte_rendu_analyse (id_analyse, utilisateur_id, nom_patient, date_prelevement, resultat, interpretation, commentaire, date_creation) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$cr->getIdAnalyse(), $cr->getUtilisateurId(), $cr->getNomPatient(), $cr->getDatePrelevement(), $cr->getResultat(), $cr->getInterpretation(), $cr->getCommentaire(), $cr->getDateCreation()]);
        }
        return [];
    }

    public function supprimerCompteRendu(int $id): void
    {
        exigerRole(['ADMIN','RESPONSABLE_LABO']);
        verifierCsrf();
        connexion()->prepare('DELETE FROM compte_rendu_analyse WHERE id = ?')->execute([$id]);
    }
}
