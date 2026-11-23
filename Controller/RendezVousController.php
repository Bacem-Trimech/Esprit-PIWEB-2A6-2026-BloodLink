<?php
declare(strict_types=1);

final class RendezVousController
{
    public function listerPraticiens(?string $specialite = null, ?int $etabId = null): array
    {
        $sql = 'SELECT p.*, e.nom AS etablissement_nom, e.ville AS etablissement_ville
                FROM praticiens p
                LEFT JOIN etablissements_sante e ON e.id = p.etablissement_id
                WHERE 1=1';
        $params = [];
        if ($specialite !== null && trim($specialite) !== '') {
            $sql .= ' AND p.specialite = :spec';
            $params['spec'] = trim($specialite);
        }
        if ($etabId !== null && $etabId > 0) {
            $sql .= ' AND p.etablissement_id = :etab';
            $params['etab'] = $etabId;
        }
        $sql .= ' ORDER BY p.specialite ASC, p.nom ASC';
        $stmt = connexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function specialites(): array
    {
        return connexion()->query('SELECT DISTINCT specialite FROM praticiens ORDER BY specialite ASC')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function trouverPraticien(int $id): ?array
    {
        $stmt = connexion()->prepare('SELECT * FROM praticiens WHERE id_praticien = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function sauverPraticien(array $post, ?int $id = null): array
    {
        exigerRole(['ADMIN','RESPONSABLE_RDV']);
        verifierCsrf();

        $nom = trim((string)($post['nom'] ?? ''));
        $prenom = trim((string)($post['prenom'] ?? ''));
        $specialite = trim((string)($post['specialite'] ?? ''));
        $telephone = trim((string)($post['telephone'] ?? ''));
        $email = trim((string)($post['email'] ?? ''));
        $adresse = trim((string)($post['adresse'] ?? ''));
        $etabId = (int)($post['etablissement_id'] ?? 0);

        $errors = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) $errors[] = 'Nom du praticien invalide (2 à 100 caractères).';
        if (mb_strlen($prenom) < 2 || mb_strlen($prenom) > 100) $errors[] = 'Prénom du praticien invalide (2 à 100 caractères).';
        if (mb_strlen($specialite) < 2 || mb_strlen($specialite) > 100) $errors[] = 'Spécialité invalide.';
        if ($telephone !== '' && !preg_match('/^[+0-9 ()-]{6,30}$/', $telephone)) $errors[] = 'Téléphone invalide.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
        if ($errors) return $errors;

        $praticien = new Praticien(
            $id,
            $nom,
            $prenom,
            $specialite,
            $telephone !== '' ? $telephone : null,
            $email !== '' ? $email : null,
            $adresse !== '' ? $adresse : null,
            $etabId > 0 ? $etabId : null
        );

        if ($id) {
            $stmt = connexion()->prepare('UPDATE praticiens SET nom=?, prenom=?, specialite=?, telephone=?, email=?, adresse=?, etablissement_id=? WHERE id_praticien=?');
            $stmt->execute([$praticien->getNom(), $praticien->getPrenom(), $praticien->getSpecialite(), $praticien->getTelephone(), $praticien->getEmail(), $praticien->getAdresse(), $praticien->getEtablissementId(), $id]);
        } else {
            $stmt = connexion()->prepare('INSERT INTO praticiens (nom, prenom, specialite, telephone, email, adresse, etablissement_id) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$praticien->getNom(), $praticien->getPrenom(), $praticien->getSpecialite(), $praticien->getTelephone(), $praticien->getEmail(), $praticien->getAdresse(), $praticien->getEtablissementId()]);
        }
        return [];
    }

    public function supprimerPraticien(int $id): void
    {
        exigerRole(['ADMIN','RESPONSABLE_RDV']);
        verifierCsrf();
        connexion()->prepare('DELETE FROM praticiens WHERE id_praticien = ?')->execute([$id]);
    }

    public function listerRendezVous(?int $utilisateurId = null): array
    {
        $sql = 'SELECT r.*, p.nom AS praticien_nom, p.prenom AS praticien_prenom, p.specialite,
                       e.nom AS etablissement_nom, e.ville AS etablissement_ville,
                       u.nom AS compte_nom, u.email AS compte_email
                FROM rendez_vous r
                JOIN praticiens p ON p.id_praticien = r.id_praticien
                LEFT JOIN etablissements_sante e ON e.id = p.etablissement_id
                LEFT JOIN utilisateurs u ON u.id = r.utilisateur_id';
        if ($utilisateurId !== null) {
            $stmt = connexion()->prepare($sql . ' WHERE r.utilisateur_id = ? ORDER BY r.date_rendezvous DESC, r.heure_rendezvous DESC');
            $stmt->execute([$utilisateurId]);
            return $stmt->fetchAll();
        }
        return connexion()->query($sql . ' ORDER BY r.date_rendezvous DESC, r.heure_rendezvous DESC')->fetchAll();
    }

    public function reserverRendezVous(array $post): array
    {
        $user = exigerConnexion();
        verifierCsrf();

        $praticienId = (int)($post['id_praticien'] ?? 0);
        $date = trim((string)($post['date_rendezvous'] ?? ''));
        $heure = trim((string)($post['heure_rendezvous'] ?? ''));
        $motif = trim((string)($post['motif'] ?? ''));
        $patientNom = trim((string)($post['patient_nom'] ?? $user['nom']));
        $patientTel = trim((string)($post['patient_telephone'] ?? ($user['telephone'] ?? '')));
        $statut = (in_array($user['role'], ['ADMIN', 'RESPONSABLE_RDV'], true) && in_array((string)($post['statut'] ?? ''), RendezVous::STATUTS, true))
            ? (string)$post['statut']
            : 'En attente';

        $errors = [];
        if ($praticienId <= 0 || !$this->trouverPraticien($praticienId)) $errors[] = 'Sélectionnez un praticien valide.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) $errors[] = 'Choisissez une date de rendez-vous valide (aujourd’hui ou ultérieure).';
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $heure)) $errors[] = 'Choisissez un horaire valide (format 24h, ex. 10:30).';
        if (mb_strlen($motif) < 4 || mb_strlen($motif) > 500) $errors[] = 'Précisez le motif de consultation (4 à 500 caractères).';
        if (mb_strlen($patientNom) < 2) $errors[] = 'Nom du patient requis.';
        if ($errors) return $errors;

        $heureSql = strlen($heure) === 5 ? $heure . ':00' : $heure;
        $check = connexion()->prepare("SELECT id_rendezvous FROM rendez_vous WHERE id_praticien = ? AND date_rendezvous = ? AND heure_rendezvous = ? AND statut <> 'Annulé' LIMIT 1");
        $check->execute([$praticienId, $date, $heureSql]);
        if ($check->fetch()) {
            return ['Ce praticien a déjà un rendez-vous confirmé ou en attente sur ce créneau exact. Choisissez un autre horaire.'];
        }

        $rdv = new RendezVous(null, $date, $heureSql, $motif, $statut, $praticienId, (int)$user['id'], $patientNom, $patientTel !== '' ? $patientTel : null);
        $stmt = connexion()->prepare('INSERT INTO rendez_vous (date_rendezvous, heure_rendezvous, motif, statut, id_praticien, utilisateur_id, patient_nom, patient_telephone) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$rdv->getDateRendezVous(), $rdv->getHeureRendezVous(), $rdv->getMotif(), $rdv->getStatut(), $rdv->getIdPraticien(), $rdv->getUtilisateurId(), $rdv->getPatientNom(), $rdv->getPatientTelephone()]);
        return [];
    }

    public function changerStatut(int $id, string $statut): void
    {
        exigerRole(['ADMIN','RESPONSABLE_RDV']);
        verifierCsrf();
        if (!in_array($statut, RendezVous::STATUTS, true)) {
            throw new RuntimeException('Statut de rendez-vous invalide.');
        }
        connexion()->prepare('UPDATE rendez_vous SET statut = ? WHERE id_rendezvous = ?')->execute([$statut, $id]);
    }

    public function annulerParUtilisateur(int $id): void
    {
        $user = exigerConnexion();
        verifierCsrf();
        if (in_array($user['role'], ['ADMIN', 'RESPONSABLE_RDV'], true)) {
            connexion()->prepare("UPDATE rendez_vous SET statut = 'Annulé' WHERE id_rendezvous = ?")->execute([$id]);
            return;
        }
        $stmt = connexion()->prepare("UPDATE rendez_vous SET statut = 'Annulé' WHERE id_rendezvous = ? AND utilisateur_id = ?");
        $stmt->execute([$id, (int)$user['id']]);
    }

    public function supprimerRendezVous(int $id): void
    {
        exigerRole(['ADMIN','RESPONSABLE_RDV']);
        verifierCsrf();
        connexion()->prepare('DELETE FROM rendez_vous WHERE id_rendezvous = ?')->execute([$id]);
    }
}
