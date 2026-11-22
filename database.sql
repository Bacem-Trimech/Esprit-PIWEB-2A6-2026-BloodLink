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
