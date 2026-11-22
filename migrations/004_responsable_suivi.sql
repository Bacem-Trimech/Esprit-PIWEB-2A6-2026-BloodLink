USE bloodlink_piweb_2a6;
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
