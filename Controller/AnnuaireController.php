<?php
declare(strict_types=1);

final class AnnuaireController
{
    public function listerEtablissements(?string $type = null, ?string $ville = null, ?string $q = null): array
    {
        $sql = 'SELECT e.*,
                       (SELECT COUNT(*) FROM services_medicaux s WHERE s.etablissement_id = e.id) AS nb_services,
                       (SELECT COUNT(*) FROM praticiens p WHERE p.etablissement_id = e.id) AS nb_praticiens,
                       (SELECT COUNT(*) FROM analyse_medicale a WHERE a.etablissement_id = e.id) AS nb_analyses,
                       (SELECT MAX(g.end_date) FROM gardes_pharmacie g WHERE g.etablissement_id = e.id AND CURDATE() BETWEEN g.start_date AND g.end_date) AS garde_jusquau
                FROM etablissements_sante e
                WHERE 1=1';
        $params = [];
        if ($type !== null && $type !== '' && array_key_exists($type, EtablissementSante::TYPES)) {
            $sql .= ' AND e.type = :type';
            $params['type'] = $type;
        }
        if ($ville !== null && trim($ville) !== '') {
            $sql .= ' AND e.ville = :ville';
            $params['ville'] = trim($ville);
        }
        if ($q !== null && trim($q) !== '') {
            $sql .= ' AND (e.nom LIKE :q1 OR e.ville LIKE :q2 OR e.adresse LIKE :q3 OR e.description LIKE :q4)';
            $like = '%' . trim($q) . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
            $params['q4'] = $like;
        }
        $sql .= ' ORDER BY e.type ASC, e.nom ASC';
        $stmt = connexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function villes(): array
    {
        return connexion()->query('SELECT DISTINCT ville FROM etablissements_sante ORDER BY ville ASC')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function trouverEtablissement(int $id): ?array
    {
        $stmt = connexion()->prepare('SELECT * FROM etablissements_sante WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function detailComplet(int $id): ?array
    {
        $etab = $this->trouverEtablissement($id);
        if (!$etab) {
            return null;
        }
        $db = connexion();

        $qServ = $db->prepare('SELECT * FROM services_medicaux WHERE etablissement_id = ? ORDER BY est_urgence DESC, nom ASC');
        $qServ->execute([$id]);
        $etab['services'] = $qServ->fetchAll();

        $qPrat = $db->prepare('SELECT * FROM praticiens WHERE etablissement_id = ? ORDER BY nom ASC');
        $qPrat->execute([$id]);
        $etab['praticiens'] = $qPrat->fetchAll();

        $qAnal = $db->prepare('SELECT * FROM analyse_medicale WHERE etablissement_id = ? ORDER BY nom ASC');
        $qAnal->execute([$id]);
        $etab['analyses'] = $qAnal->fetchAll();

        $qGarde = $db->prepare('SELECT * FROM gardes_pharmacie WHERE etablissement_id = ? ORDER BY start_date DESC LIMIT 5');
        $qGarde->execute([$id]);
        $etab['gardes'] = $qGarde->fetchAll();

        $qStock = $db->prepare('SELECT d.quantity, m.name, m.dci, m.dosage, m.form, m.requires_prescription FROM disponibilites d JOIN medicaments m ON m.id = d.medicament_id WHERE d.etablissement_id = ? AND d.quantity > 0 ORDER BY m.name ASC');
        $qStock->execute([$id]);
        $etab['stock'] = $qStock->fetchAll();

        $qCollectes = $db->prepare("SELECT id, titre, ville, debut, fin FROM collectes WHERE statut = 'PUBLIEE' AND TRIM(LOWER(ville)) = TRIM(LOWER(?)) ORDER BY debut ASC LIMIT 3");
        $qCollectes->execute([(string)$etab['ville']]);
        $etab['collectes_ville'] = $qCollectes->fetchAll();

        return $etab;
    }

    public function sauverEtablissement(array $post, ?int $id = null): array
    {
        exigerRole(['ADMIN','RESPONSABLE_ANNUAIRE']);
        verifierCsrf();

        $nom = trim((string)($post['nom'] ?? ''));
        $type = trim((string)($post['type'] ?? 'hopital'));
        $ville = trim((string)($post['ville'] ?? ''));
        $adresse = trim((string)($post['adresse'] ?? ''));
        $telephone = trim((string)($post['telephone'] ?? ''));
        $email = trim((string)($post['email'] ?? ''));
        $description = trim((string)($post['description'] ?? ''));

        $errors = [];
        if (mb_strlen($nom) < 3 || mb_strlen($nom) > 160) $errors[] = 'Le nom de l’établissement doit contenir entre 3 et 160 caractères.';
        if (!array_key_exists($type, EtablissementSante::TYPES)) $errors[] = 'Type d’établissement invalide.';
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) $errors[] = 'Ville invalide (2 à 100 caractères).';
        if (mb_strlen($adresse) < 5 || mb_strlen($adresse) > 255) $errors[] = 'Adresse invalide (5 à 255 caractères).';
        if ($telephone !== '' && !preg_match('/^[+0-9 ()-]{6,30}$/', $telephone)) $errors[] = 'Numéro de téléphone invalide.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
        if ($errors) return $errors;

        $check = connexion()->prepare('SELECT id FROM etablissements_sante WHERE nom = ? AND id <> ?');
        $check->execute([$nom, (int)($id ?? 0)]);
        if ($check->fetch()) {
            return ['Un établissement portant ce nom existe déjà dans l’annuaire.'];
        }

        $etab = new EtablissementSante($id, $nom, $type, $ville, $adresse, $telephone !== '' ? $telephone : null, $email !== '' ? $email : null, $description !== '' ? $description : null);

        if ($id) {
            $stmt = connexion()->prepare('UPDATE etablissements_sante SET nom=?, type=?, ville=?, adresse=?, telephone=?, email=?, description=? WHERE id=?');
            $stmt->execute([$etab->getNom(), $etab->getType(), $etab->getVille(), $etab->getAdresse(), $etab->getTelephone(), $etab->getEmail(), $etab->getDescription(), $id]);
        } else {
            $stmt = connexion()->prepare('INSERT INTO etablissements_sante (nom, type, ville, adresse, telephone, email, description) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$etab->getNom(), $etab->getType(), $etab->getVille(), $etab->getAdresse(), $etab->getTelephone(), $etab->getEmail(), $etab->getDescription()]);
        }
        return [];
    }

    public function supprimerEtablissement(int $id): void
    {
        exigerRole(['ADMIN','RESPONSABLE_ANNUAIRE']);
        verifierCsrf();
        connexion()->prepare('DELETE FROM etablissements_sante WHERE id = ?')->execute([$id]);
    }

    public function listerServices(): array
    {
        return connexion()->query('SELECT s.*, e.nom AS etablissement_nom, e.ville AS etablissement_ville FROM services_medicaux s JOIN etablissements_sante e ON e.id = s.etablissement_id ORDER BY e.nom ASC, s.est_urgence DESC, s.nom ASC')->fetchAll();
    }

    public function sauverService(array $post, ?int $id = null): array
    {
        exigerRole(['ADMIN','RESPONSABLE_ANNUAIRE']);
        verifierCsrf();

        $etabId = (int)($post['etablissement_id'] ?? 0);
        $nom = trim((string)($post['nom'] ?? ''));
        $specialite = trim((string)($post['specialite'] ?? ''));
        $horaires = trim((string)($post['horaires'] ?? ''));
        $telephone = trim((string)($post['telephone'] ?? ''));
        $estUrgence = !empty($post['est_urgence']);
        $description = trim((string)($post['description'] ?? ''));

        $errors = [];
        if ($etabId <= 0 || !$this->trouverEtablissement($etabId)) $errors[] = 'Choisissez un établissement de santé valide.';
        if (mb_strlen($nom) < 3 || mb_strlen($nom) > 120) $errors[] = 'Le nom du service doit contenir entre 3 et 120 caractères.';
        if (mb_strlen($specialite) < 2 || mb_strlen($specialite) > 120) $errors[] = 'Spécialité invalide.';
        if ($telephone !== '' && !preg_match('/^[+0-9 ()-]{6,30}$/', $telephone)) $errors[] = 'Téléphone invalide.';
        if ($errors) return $errors;

        $service = new ServiceMedical($id, $etabId, $nom, $specialite, $horaires !== '' ? $horaires : null, $telephone !== '' ? $telephone : null, $estUrgence, $description !== '' ? $description : null);

        if ($id) {
            $stmt = connexion()->prepare('UPDATE services_medicaux SET etablissement_id=?, nom=?, specialite=?, horaires=?, telephone=?, est_urgence=?, description=? WHERE id=?');
            $stmt->execute([$service->getEtablissementId(), $service->getNom(), $service->getSpecialite(), $service->getHoraires(), $service->getTelephone(), $service->isEstUrgence() ? 1 : 0, $service->getDescription(), $id]);
        } else {
            $stmt = connexion()->prepare('INSERT INTO services_medicaux (etablissement_id, nom, specialite, horaires, telephone, est_urgence, description) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$service->getEtablissementId(), $service->getNom(), $service->getSpecialite(), $service->getHoraires(), $service->getTelephone(), $service->isEstUrgence() ? 1 : 0, $service->getDescription()]);
        }
        return [];
    }

    public function supprimerService(int $id): void
    {
        exigerRole(['ADMIN','RESPONSABLE_ANNUAIRE']);
        verifierCsrf();
        connexion()->prepare('DELETE FROM services_medicaux WHERE id = ?')->execute([$id]);
    }
}
