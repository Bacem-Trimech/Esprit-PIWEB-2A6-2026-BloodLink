CREATE DATABASE IF NOT EXISTS bloodlink_piweb_2a6 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bloodlink_piweb_2a6;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('DONNEUR','RESPONSABLE','ADMIN') NOT NULL DEFAULT 'DONNEUR',
    actif TINYINT(1) NOT NULL DEFAULT 1,
    invitation_hash CHAR(64) NULL,
    invitation_expire DATETIME NULL,
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS collectes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(160) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    debut DATETIME NOT NULL,
    fin DATETIME NOT NULL,
    statut ENUM('BROUILLON','PUBLIEE','CLOTUREE') NOT NULL DEFAULT 'BROUILLON',
    image_url VARCHAR(255) NULL,
    galerie_json TEXT NULL,
    organisateur_id BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_collecte_organisateur FOREIGN KEY (organisateur_id) REFERENCES utilisateurs(id),
    INDEX idx_collectes_publiques (statut, debut, ville)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS creneaux (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    collecte_id BIGINT UNSIGNED NOT NULL,
    debut DATETIME NOT NULL,
    fin DATETIME NOT NULL,
    capacite SMALLINT UNSIGNED NOT NULL,
    statut ENUM('OUVERT','FERME') NOT NULL DEFAULT 'OUVERT',
    CONSTRAINT fk_creneau_collecte FOREIGN KEY (collecte_id) REFERENCES collectes(id) ON DELETE CASCADE,
    INDEX idx_creneau_collecte (collecte_id, debut)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS participations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    collecte_id BIGINT UNSIGNED NOT NULL,
    creneau_id BIGINT UNSIGNED NULL,
    donneur_id BIGINT UNSIGNED NOT NULL,
    mode ENUM('EN_LIGNE','SUR_PLACE') NOT NULL,
    statut ENUM('CONFIRMEE','ANNULEE','ARRIVEE','ABSENTE') NOT NULL DEFAULT 'CONFIRMEE',
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    arrivee_le DATETIME NULL,
    code_confirmation CHAR(12) NOT NULL UNIQUE,
    CONSTRAINT fk_participation_collecte FOREIGN KEY (collecte_id) REFERENCES collectes(id),
    CONSTRAINT fk_participation_creneau FOREIGN KEY (creneau_id) REFERENCES creneaux(id),
    CONSTRAINT fk_participation_donneur FOREIGN KEY (donneur_id) REFERENCES utilisateurs(id),
    INDEX idx_participation_creneau (creneau_id, statut),
    INDEX idx_participation_donneur (donneur_id, cree_le)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dons_sang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    participation_id BIGINT UNSIGNED NOT NULL UNIQUE,
    effectue_le DATETIME NOT NULL,
    confirme_par BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_don_participation FOREIGN KEY (participation_id) REFERENCES participations(id),
    CONSTRAINT fk_don_agent FOREIGN KEY (confirme_par) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

-- Fonctionnalités avancées (profil, clinique, SOS et paramètres)

ALTER TABLE utilisateurs
  ADD COLUMN telephone VARCHAR(30) NULL,
  ADD COLUMN ville VARCHAR(100) NULL,
  ADD COLUMN date_naissance DATE NULL,
  ADD COLUMN groupe_sanguin ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NULL,
  ADD COLUMN profil_complet TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN avatar_couleur CHAR(7) NULL,
  ADD COLUMN qr_identifiant CHAR(48) NULL UNIQUE,
  ADD COLUMN preferences_contact TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE utilisateurs MODIFY invitation_expire DATETIME NULL;
ALTER TABLE dons_sang
  ADD COLUMN volume_ml SMALLINT UNSIGNED NULL,
  ADD COLUMN numero_poche VARCHAR(60) NULL UNIQUE;
CREATE TABLE IF NOT EXISTS examens_pre_don (
  participation_id BIGINT UNSIGNED PRIMARY KEY,
  tension_systolique SMALLINT UNSIGNED NULL,
  tension_diastolique SMALLINT UNSIGNED NULL,
  pouls SMALLINT UNSIGNED NULL,
  temperature_c DECIMAL(4,1) NULL,
  hemoglobine_g_dl DECIMAL(4,1) NULL,
  poids_kg DECIMAL(5,1) NULL,
  decision ENUM('EN_ATTENTE','APTE','AJOURNE') NOT NULL DEFAULT 'EN_ATTENTE',
  saisi_par BIGINT UNSIGNED NOT NULL,
  saisi_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (participation_id) REFERENCES participations(id),
  FOREIGN KEY (saisi_par) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS resultats_laboratoire (
  participation_id BIGINT UNSIGNED PRIMARY KEY,
  groupe_confirme ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NULL,
  statut ENUM('EN_ATTENTE','VALIDE','CONTACT_CENTRE') NOT NULL DEFAULT 'EN_ATTENTE',
  saisi_par BIGINT UNSIGNED NOT NULL,
  saisi_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (participation_id) REFERENCES participations(id),
  FOREIGN KEY (saisi_par) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS alertes_sos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lieu VARCHAR(200) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  groupe_receveur ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
  telephone VARCHAR(30) NOT NULL,
  message VARCHAR(500) NULL,
  ip_hash CHAR(64) NOT NULL,
  cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expire_le DATETIME NOT NULL,
  supprime_le DATETIME NULL,
  INDEX idx_sos_visibles (expire_le,supprime_le),
  INDEX idx_sos_limite (ip_hash,cree_le)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS parametres (
  cle VARCHAR(60) PRIMARY KEY,
  valeur VARCHAR(500) NOT NULL,
  modifie_par BIGINT UNSIGNED NULL,
  modifie_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (modifie_par) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;
INSERT IGNORE INTO parametres (cle,valeur) VALUES
('nom_organisation','BloodLink'),
('contact_public',''),
('capacite_defaut','2'),
('horizon_reservation_jours','60'),
('message_accueil','Un don de sang. Des vies sauvées.');

CREATE TABLE IF NOT EXISTS statistiques_jour (
  jour DATE PRIMARY KEY,
  vues INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

ALTER TABLE resultats_laboratoire
  ADD COLUMN vih ENUM('EN_ATTENTE','NON_REACTIF','A_CONFIRMER') NOT NULL DEFAULT 'EN_ATTENTE',
  ADD COLUMN vhb ENUM('EN_ATTENTE','NON_REACTIF','A_CONFIRMER') NOT NULL DEFAULT 'EN_ATTENTE',
  ADD COLUMN vhc ENUM('EN_ATTENTE','NON_REACTIF','A_CONFIRMER') NOT NULL DEFAULT 'EN_ATTENTE',
  ADD COLUMN syphilis ENUM('EN_ATTENTE','NON_REACTIF','A_CONFIRMER') NOT NULL DEFAULT 'EN_ATTENTE';

CREATE TABLE IF NOT EXISTS parametres_responsable (
  responsable_id BIGINT UNSIGNED PRIMARY KEY,
  nom_organisation VARCHAR(100) NOT NULL DEFAULT 'BloodLink',
  contact_public VARCHAR(190) NOT NULL DEFAULT '',
  capacite_defaut SMALLINT UNSIGNED NOT NULL DEFAULT 2,
  horizon_reservation_jours SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  message_accueil VARCHAR(300) NOT NULL DEFAULT 'Bienvenue à notre collecte de sang.',
  FOREIGN KEY (responsable_id) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;
ALTER TABLE resultats_laboratoire
  ADD COLUMN statut_prochain_don ENUM('EN_COURS','AUTORISE','NON_AUTORISE') NOT NULL DEFAULT 'EN_COURS',
  ADD COLUMN prochain_don_le DATE NULL;


-- =========================================================================
-- INTÉGRATION DES 4 AUTRES MODULES DU PORTAIL SANTÉ (ANNUAIRE, RDV, PHARMACIES, LABO)
-- =========================================================================

CREATE TABLE IF NOT EXISTS etablissements_sante (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services_medicaux (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etablissement_id INT UNSIGNED NOT NULL,
  nom VARCHAR(120) NOT NULL,
  specialite VARCHAR(120) NOT NULL,
  horaires VARCHAR(120) NULL,
  telephone VARCHAR(30) NULL,
  est_urgence TINYINT(1) NOT NULL DEFAULT 0,
  description TEXT NULL,
  CONSTRAINT fk_service_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS praticiens (
  id_praticien INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  specialite VARCHAR(100) NOT NULL,
  telephone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  adresse VARCHAR(255) NULL,
  etablissement_id INT UNSIGNED NULL,
  CONSTRAINT fk_praticien_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rendez_vous (
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
  CONSTRAINT fk_rdv_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicaments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  dci VARCHAR(120) NULL,
  form VARCHAR(60) NOT NULL,
  dosage VARCHAR(60) NULL,
  requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_medicament (name, dosage, form)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gardes_pharmacie (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etablissement_id INT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  notes VARCHAR(200) NULL,
  CONSTRAINT fk_garde_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS disponibilites (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etablissement_id INT UNSIGNED NOT NULL,
  medicament_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_dispo (etablissement_id, medicament_id),
  CONSTRAINT fk_dispo_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE,
  CONSTRAINT fk_dispo_med FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS analyse_medicale (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS compte_rendu_analyse (
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
) ENGINE=InnoDB;
