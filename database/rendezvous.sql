CREATE DATABASE IF NOT EXISTS rendezvous_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE rendezvous_db;

CREATE TABLE IF NOT EXISTS Praticien (
    id_praticien INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    specialite VARCHAR(100),
    telephone VARCHAR(20),
    email VARCHAR(150),
    adresse VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS RendezVous (
    id_rendezvous INT AUTO_INCREMENT PRIMARY KEY,
    date_rendezvous DATE NOT NULL,
    heure_rendezvous TIME NOT NULL,
    motif TEXT,
    statut VARCHAR(30) DEFAULT 'En attente',
    id_praticien INT NOT NULL,
    CONSTRAINT fk_rdv_praticien FOREIGN KEY (id_praticien)
        REFERENCES Praticien(id_praticien)
        ON UPDATE CASCADE ON DELETE RESTRICT
);
