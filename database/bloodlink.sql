-- BloodLink / Pharmacies - import with phpMyAdmin (Import or SQL tab).
-- Re-importing resets the tables (DROP TABLE IF EXISTS), including the demo accounts.
CREATE DATABASE IF NOT EXISTS bloodlink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bloodlink;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS disponibilites, gardes_pharmacie, medicaments, etablissements_sante, users, roles;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================== users and roles ===============================
CREATE TABLE roles (
  id TINYINT UNSIGNED PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  label VARCHAR(60) NOT NULL,
  side ENUM('front','back') NOT NULL
) ENGINE=InnoDB;

INSERT INTO roles (id, code, label, side) VALUES
  (1, 'donneur',           'Donneur',           'front'),
  (2, 'support',           'Support',           'back'),
  (3, 'moderateur',        'Modérateur',        'back'),
  (4, 'admin_gestion',     'Admin gestion',     'back'),
  (5, 'admin_supervision', 'Admin supervision', 'back'),
  (6, 'super_admin',       'Super admin',       'back');

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  city VARCHAR(80) NULL,
  birth_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Demo accounts. The placeholder '__DEMO__' is replaced by a real bcrypt hash of DEMO_PASSWORD
-- (config/config.php) the first time the site is opened in DEMO_MODE.
INSERT INTO users (username, email, password, role_id, city, birth_date) VALUES
  ('sarra',       'donor@demo.tn',       '__DEMO__', 1, 'Nabeul', '1999-04-12'),
  ('support1',    'support@demo.tn',     '__DEMO__', 2, NULL, NULL),
  ('moderateur1', 'moderateur@demo.tn',  '__DEMO__', 3, NULL, NULL),
  ('gestion',     'gestion@demo.tn',     '__DEMO__', 4, NULL, NULL),
  ('supervision', 'supervision@demo.tn', '__DEMO__', 5, NULL, NULL),
  ('superadmin',  'super@demo.tn',       '__DEMO__', 6, NULL, NULL);

-- =============================== Pharmacies module ===============================
-- EtablissementSante belongs to the Annuaire medical module: minimal version so the JOINs work.
CREATE TABLE etablissements_sante (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  type ENUM('hopital','clinique','pharmacie','laboratoire') NOT NULL DEFAULT 'pharmacie',
  city VARCHAR(80) NOT NULL,
  address VARCHAR(200) NOT NULL,
  phone VARCHAR(30) NULL
) ENGINE=InnoDB;

-- Own entity 1: Medicament
CREATE TABLE medicaments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  dci VARCHAR(120) NULL,
  form VARCHAR(40) NOT NULL,
  dosage VARCHAR(40) NULL,
  requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_medicament (name, dosage, form)
) ENGINE=InnoDB;

-- Own entity 2: GardePharmacie (a pharmacy is on duty between start_date and end_date)
CREATE TABLE gardes_pharmacie (
  id INT AUTO_INCREMENT PRIMARY KEY,
  etablissement_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  notes VARCHAR(200) NULL,
  CONSTRAINT fk_garde_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Stock declared by a pharmacy for a medicine (links the two)
CREATE TABLE disponibilites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  etablissement_id INT NOT NULL,
  medicament_id INT NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_dispo (etablissement_id, medicament_id),
  CONSTRAINT fk_dispo_etab FOREIGN KEY (etablissement_id) REFERENCES etablissements_sante(id) ON DELETE CASCADE,
  CONSTRAINT fk_dispo_med FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO etablissements_sante (name, type, city, address, phone) VALUES
  ('Pharmacie Centrale Hammamet', 'pharmacie', 'Hammamet', 'Avenue de la République, Hammamet', '72 280 100'),
  ('Pharmacie El Amel',           'pharmacie', 'Nabeul',   'Rue Habib Thameur, Nabeul',          '72 285 200'),
  ('Pharmacie de la Médina',      'pharmacie', 'Tunis',    'Rue Jemaa Ezzitouna, Tunis',         '71 560 300'),
  ('Pharmacie Ibn Sina',          'pharmacie', 'Sousse',   'Boulevard Hassouna Ayachi, Sousse',  '73 220 400'),
  ('Pharmacie Les Jasmins',       'pharmacie', 'Hammamet', 'Rue des Jasmins, Hammamet',          '72 281 500'),
  ('Hôpital Habib Bourguiba',     'hopital',   'Sfax',     'Route El Ain, Sfax',                 '74 241 000');

INSERT INTO medicaments (name, dci, form, dosage, requires_prescription) VALUES
  ('Doliprane',   'Paracétamol',  'Comprimé', '1000 mg', 0),
  ('Efferalgan',  'Paracétamol',  'Comprimé', '500 mg',  0),
  ('Aspégic',     'Acide acétylsalicylique', 'Sachet', '500 mg', 0),
  ('Spasfon',     'Phloroglucinol', 'Comprimé', '80 mg', 0),
  ('Amoxicilline','Amoxicilline', 'Gélule',   '500 mg',  1),
  ('Ventoline',   'Salbutamol',   'Spray',    '100 µg',  1);

-- On duty today: 2 pharmacies; next week: 1 pharmacy; last week: 1 pharmacy
INSERT INTO gardes_pharmacie (etablissement_id, start_date, end_date, notes)
  SELECT id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'Garde de nuit' FROM etablissements_sante WHERE name IN ('Pharmacie Centrale Hammamet', 'Pharmacie El Amel');
INSERT INTO gardes_pharmacie (etablissement_id, start_date, end_date, notes)
  SELECT id, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 13 DAY), 'Garde de week-end' FROM etablissements_sante WHERE name = 'Pharmacie de la Médina';
INSERT INTO gardes_pharmacie (etablissement_id, start_date, end_date, notes)
  SELECT id, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL FROM etablissements_sante WHERE name = 'Pharmacie Ibn Sina';

-- Stock: every pharmacy declares every medicine, quantity 0 to 19 (0 = out of stock)
INSERT INTO disponibilites (etablissement_id, medicament_id, quantity)
  SELECT e.id, m.id, FLOOR(RAND() * 20) FROM etablissements_sante e CROSS JOIN medicaments m WHERE e.type = 'pharmacie';
