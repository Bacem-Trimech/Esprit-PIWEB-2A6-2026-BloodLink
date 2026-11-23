<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            $host = getenv('BLOODLINK_DB_HOST') ?: '127.0.0.1';
            $name = getenv('BLOODLINK_DB_NAME') ?: 'bloodlink_piweb_2a6';
            $user = getenv('BLOODLINK_DB_USER') ?: 'root';
            $pass = getenv('BLOODLINK_DB_PASS') ?: '';
            self::$pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::assurerSchemaIntegre(self::$pdo);
        }
        return self::$pdo;
    }

    private static function assurerSchemaIntegre(PDO $pdo): void
    {
        try {
            $pdo->exec("ALTER TABLE collectes ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NULL AFTER statut");
            $pdo->exec("ALTER TABLE collectes ADD COLUMN IF NOT EXISTS galerie_json TEXT NULL AFTER image_url");

            // Module 1 : Annuaire Médical (partagé avec Pharmacies, Rendez-vous et Laboratoires)
            $pdo->exec("CREATE TABLE IF NOT EXISTS etablissements_sante (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(160) NOT NULL UNIQUE,
                type ENUM('hopital','clinique','polyclinique','pharmacie','laboratoire') NOT NULL DEFAULT 'hopital',
                ville VARCHAR(100) NOT NULL,
                adresse VARCHAR(255) NOT NULL,
                telephone VARCHAR(30) NULL,
                email VARCHAR(160) NULL,
                description TEXT NULL,
                cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_etab_type_ville (type, ville)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS services_medicaux (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                etablissement_id INT UNSIGNED NOT NULL,
                nom VARCHAR(120) NOT NULL,
                specialite VARCHAR(120) NOT NULL,
                horaires VARCHAR(120) NULL,
                telephone VARCHAR(30) NULL,
                est_urgence TINYINT(1) NOT NULL DEFAULT 0,
                description TEXT NULL,
                CONSTRAINT fk_service_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE,
                INDEX idx_service_etab (etablissement_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Module 2 : Rendez-vous médicaux (Praticiens liés aux établissements + RDV liés aux utilisateurs)
            $pdo->exec("CREATE TABLE IF NOT EXISTS praticiens (
                id_praticien INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                specialite VARCHAR(100) NOT NULL,
                telephone VARCHAR(30) NULL,
                email VARCHAR(150) NULL,
                adresse VARCHAR(255) NULL,
                etablissement_id INT UNSIGNED NULL,
                CONSTRAINT fk_praticien_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE SET NULL,
                INDEX idx_praticien_spec (specialite)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS rendez_vous (
                id_rendezvous INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                date_rendezvous DATE NOT NULL,
                heure_rendezvous TIME NOT NULL,
                motif VARCHAR(500) NOT NULL,
                statut ENUM('En attente','Confirmé','Annulé','Terminé') NOT NULL DEFAULT 'En attente',
                id_praticien INT UNSIGNED NOT NULL,
                utilisateur_id BIGINT UNSIGNED NULL,
                patient_nom VARCHAR(120) NULL,
                patient_telephone VARCHAR(30) NULL,
                cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_rdv_praticien FOREIGN KEY (id_praticien) REFERENCES praticiens(id_praticien) ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT fk_rdv_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
                INDEX idx_rdv_date (date_rendezvous, id_praticien)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Module 3 : Pharmacies, Médicaments, Gardes et Stock (liés à etablissements_sante)
            $pdo->exec("CREATE TABLE IF NOT EXISTS medicaments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                dci VARCHAR(120) NULL,
                form VARCHAR(60) NOT NULL,
                dosage VARCHAR(60) NULL,
                requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_medicament (name, dosage, form)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS gardes_pharmacie (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                etablissement_id INT UNSIGNED NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                notes VARCHAR(200) NULL,
                CONSTRAINT fk_garde_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE,
                INDEX idx_garde_dates (start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS disponibilites (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                etablissement_id INT UNSIGNED NOT NULL,
                medicament_id INT UNSIGNED NOT NULL,
                quantity INT UNSIGNED NOT NULL DEFAULT 0,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_dispo (etablissement_id, medicament_id),
                CONSTRAINT fk_dispo_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE,
                CONSTRAINT fk_dispo_med FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Module 4 : Laboratoires & Analyses Médicales (liés à etablissements_sante et utilisateurs)
            $pdo->exec("CREATE TABLE IF NOT EXISTS analyse_medicale (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(120) NOT NULL,
                description VARCHAR(255) NOT NULL,
                type_echantillon VARCHAR(40) NOT NULL,
                prix DECIMAL(10,2) NOT NULL,
                delai_heures INT UNSIGNED NOT NULL,
                a_jeun TINYINT(1) NOT NULL DEFAULT 0,
                etablissement_id INT UNSIGNED NULL,
                date_ajout DATE NOT NULL,
                CONSTRAINT fk_analyse_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS compte_rendu_analyse (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_analyse INT UNSIGNED NOT NULL,
                utilisateur_id BIGINT UNSIGNED NULL,
                nom_patient VARCHAR(120) NOT NULL,
                date_prelevement DATE NOT NULL,
                resultat TEXT NOT NULL,
                interpretation ENUM('Normal','Anormal') NOT NULL DEFAULT 'Normal',
                commentaire VARCHAR(255) NULL,
                date_creation DATE NOT NULL,
                CONSTRAINT fk_cr_analyse FOREIGN KEY (id_analyse) REFERENCES analyse_medicale(id) ON DELETE CASCADE,
                CONSTRAINT fk_cr_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Données de démonstration initiales uniquement si les tables intégrées sont vides
            if ((int)$pdo->query("SELECT COUNT(*) FROM etablissements_sante")->fetchColumn() === 0) {
                $pdo->exec("INSERT INTO etablissements_sante (nom, type, ville, adresse, telephone, email, description) VALUES
                    ('Hôpital Charles-Nicolle', 'hopital', 'Tunis', 'Boulevard du 9 Avril 1938, Bab Souika, Tunis', '71 578 000', 'contact@charlesnicolle.tn', 'Centre hospitalo-universitaire de référence doté d’un service d’urgences 24/7 et d’une unité de transfusion.'),
                    ('Clinique El Manar', 'clinique', 'Tunis', 'Avenue Habib Bourguiba, El Manar 2, Tunis', '71 885 000', 'accueil@clinique-elmanar.tn', 'Clinique pluridisciplinaire : cardiologie, hématologie, radiologie et consultations spécialisées.'),
                    ('Hôpital Habib Bourguiba', 'hopital', 'Sfax', 'Route El Ain, Sfax', '74 241 000', 'contact@chu-sfax.tn', 'Établissement public hospitalier régional avec pôle urgences et chirurgie.'),
                    ('Pharmacie Centrale Hammamet', 'pharmacie', 'Hammamet', 'Avenue de la République, Hammamet', '72 280 100', 'centrale.hammamet@pharma.tn', 'Officine de garde de nuit et dispensation complète.'),
                    ('Pharmacie El Amel', 'pharmacie', 'Nabeul', 'Rue Habib Thameur, Nabeul', '72 285 200', 'elamel.nabeul@pharma.tn', 'Pharmacie de jour et de garde avec stock vérifié.'),
                    ('Pharmacie de la Médina', 'pharmacie', 'Tunis', 'Rue Jemaa Ezzitouna, Tunis', '71 560 300', 'medina.tunis@pharma.tn', 'Pharmacie au cœur de Tunis.'),
                    ('Pharmacie Ibn Sina', 'pharmacie', 'Sousse', 'Boulevard Hassouna Ayachi, Sousse', '73 220 400', 'ibnsina.sousse@pharma.tn', 'Pharmacie centrale de Sousse.'),
                    ('Laboratoire Pasteur Tunis', 'laboratoire', 'Tunis', '13 Place Pasteur, Belvédère, Tunis', '71 783 022', 'labo@pasteur-tunis.tn', 'Laboratoire d’analyses médicales, hématologie, biochimie et sérologie.'),
                    ('Laboratoire BioSanté Ariana', 'laboratoire', 'Ariana', 'Avenue Habib Bourguiba, Ariana', '71 709 450', 'contact@biosante-ariana.tn', 'Analyses biologiques rapides et prélèvements sur rendez-vous.')");

                $pdo->exec("INSERT INTO services_medicaux (etablissement_id, nom, specialite, horaires, telephone, est_urgence, description)
                    SELECT id, 'Urgences Médico-Chirurgicales', 'Médecine d’urgence', '24h/24 · 7j/7', '71 578 111', 1, 'Accueil des urgences vitales et transfusions immédiates.' FROM etablissements_sante WHERE nom='Hôpital Charles-Nicolle'
                    UNION ALL
                    SELECT id, 'Service d’Hématologie', 'Hématologie & Transfusion', 'Lun–Sam 08:00–16:00', '71 578 220', 0, 'Suivi hématologique et coordination avec les collectes BloodLink.' FROM etablissements_sante WHERE nom='Hôpital Charles-Nicolle'
                    UNION ALL
                    SELECT id, 'Pôle Cardiologie', 'Cardiologie', 'Lun–Ven 08:30–17:30', '71 885 120', 0, 'Consultations cardiologiques, ECG et bilans pré-opératoires.' FROM etablissements_sante WHERE nom='Clinique El Manar'
                    UNION ALL
                    SELECT id, 'Urgences Régionales Sfax', 'Urgences', '24h/24 · 7j/7', '74 241 190', 1, 'Prise en charge d’urgence 24h/24.' FROM etablissements_sante WHERE nom='Hôpital Habib Bourguiba'");
            }

            if ((int)$pdo->query("SELECT COUNT(*) FROM praticiens")->fetchColumn() === 0) {
                $pdo->exec("INSERT INTO praticiens (nom, prenom, specialite, telephone, email, adresse, etablissement_id)
                    SELECT 'Ben Salem', 'Amira', 'Hématologie', '71 578 301', 'amira.bensalem@charlesnicolle.tn', 'Hôpital Charles-Nicolle, Tunis', id FROM etablissements_sante WHERE nom='Hôpital Charles-Nicolle'
                    UNION ALL
                    SELECT 'Trabelsi', 'Karim', 'Cardiologie', '71 885 210', 'k.trabelsi@clinique-elmanar.tn', 'Clinique El Manar, Tunis', id FROM etablissements_sante WHERE nom='Clinique El Manar'
                    UNION ALL
                    SELECT 'Gharbi', 'Sonia', 'Médecine Générale', '71 709 800', 's.gharbi@clinique-elmanar.tn', 'El Manar 2, Tunis', id FROM etablissements_sante WHERE nom='Clinique El Manar'
                    UNION ALL
                    SELECT 'Kallel', 'Mehdi', 'Médecine Interne', '74 241 330', 'm.kallel@chu-sfax.tn', 'Hôpital Habib Bourguiba, Sfax', id FROM etablissements_sante WHERE nom='Hôpital Habib Bourguiba'");

                $donorId = $pdo->query("SELECT id FROM utilisateurs WHERE role='DONNEUR' ORDER BY id ASC LIMIT 1")->fetchColumn();
                $donorIdSql = $donorId ? (string)(int)$donorId : 'NULL';
                $pdo->exec("INSERT INTO rendez_vous (date_rendezvous, heure_rendezvous, motif, statut, id_praticien, utilisateur_id, patient_nom, patient_telephone)
                    SELECT DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Bilan hématologique de suivi post-don', 'Confirmé', id_praticien, {$donorIdSql}, 'Donneur Démo Un', '216 20000010' FROM praticiens WHERE nom='Ben Salem' LIMIT 1");
            }

            if ((int)$pdo->query("SELECT COUNT(*) FROM medicaments")->fetchColumn() === 0) {
                $pdo->exec("INSERT INTO medicaments (name, dci, form, dosage, requires_prescription) VALUES
                    ('Doliprane', 'Paracétamol', 'Comprimé', '1000 mg', 0),
                    ('Efferalgan', 'Paracétamol', 'Comprimé effervescent', '500 mg', 0),
                    ('Tardyferon', 'Sulfate ferreux', 'Comprimé', '80 mg', 0),
                    ('Aspégic', 'Acide acétylsalicylique', 'Sachet', '500 mg', 0),
                    ('Amoxicilline', 'Amoxicilline', 'Gélule', '500 mg', 1),
                    ('Ventoline', 'Salbutamol', 'Spray', '100 µg', 1)");

                $pdo->exec("INSERT INTO gardes_pharmacie (etablissement_id, start_date, end_date, notes)
                    SELECT id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'Garde de nuit 24h/24' FROM etablissements_sante WHERE nom IN ('Pharmacie Centrale Hammamet', 'Pharmacie El Amel', 'Pharmacie de la Médina')
                    UNION ALL
                    SELECT id, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 13 DAY), 'Garde semaine prochaine' FROM etablissements_sante WHERE nom = 'Pharmacie Ibn Sina'");

                $pdo->exec("INSERT INTO disponibilites (etablissement_id, medicament_id, quantity)
                    SELECT e.id, m.id, ((e.id * 7 + m.id * 5) % 18) + 2
                    FROM etablissements_sante e
                    CROSS JOIN medicaments m
                    WHERE e.type = 'pharmacie'");
            }

            if ((int)$pdo->query("SELECT COUNT(*) FROM analyse_medicale")->fetchColumn() === 0) {
                $pdo->exec("INSERT INTO analyse_medicale (nom, description, type_echantillon, prix, delai_heures, a_jeun, etablissement_id, date_ajout)
                    SELECT 'Numération formule sanguine (NFS)', 'Mesure des globules rouges, blancs, hémoglobine et plaquettes', 'Sang', 18.00, 24, 0, id, CURDATE() FROM etablissements_sante WHERE nom='Laboratoire Pasteur Tunis'
                    UNION ALL
                    SELECT 'Glycémie à jeun', 'Dosage du taux de glucose dans le sang', 'Sang', 6.50, 4, 1, id, CURDATE() FROM etablissements_sante WHERE nom='Laboratoire Pasteur Tunis'
                    UNION ALL
                    SELECT 'Bilan martial (Ferritine & Fer)', 'Contrôle des réserves en fer après don de sang', 'Sang', 28.00, 24, 1, id, CURDATE() FROM etablissements_sante WHERE nom='Laboratoire BioSanté Ariana'
                    UNION ALL
                    SELECT 'Bilan lipidique complet', 'Cholestérol total, HDL, LDL et triglycérides', 'Sang', 30.00, 24, 1, id, CURDATE() FROM etablissements_sante WHERE nom='Laboratoire BioSanté Ariana'");

                $donorRow = $pdo->query("SELECT id, nom FROM utilisateurs WHERE role='DONNEUR' ORDER BY id ASC LIMIT 1")->fetch();
                $uidSql = $donorRow ? (string)(int)$donorRow['id'] : 'NULL';
                $unameSql = $donorRow ? $pdo->quote((string)$donorRow['nom']) : "'Donneur Démo Un'";
                $pdo->exec("INSERT INTO compte_rendu_analyse (id_analyse, utilisateur_id, nom_patient, date_prelevement, resultat, interpretation, commentaire, date_creation)
                    SELECT id, {$uidSql}, {$unameSql}, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Hémoglobine : 14,2 g/dL · Globules rouges : 4,8 M/mm³ · Plaquettes : 250 000/mm³', 'Normal', 'Bilan hématologique conforme pour un futur don.', CURDATE()
                    FROM analyse_medicale WHERE nom LIKE 'Numération%' LIMIT 1");
            }
        } catch (Throwable) {
        }
    }
}

final class Config extends Database {}

function connexion(): PDO
{
    return Database::getConnexion();
}
