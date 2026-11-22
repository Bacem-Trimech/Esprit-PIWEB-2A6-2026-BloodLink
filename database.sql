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
