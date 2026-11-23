# BloodLink — Plateforme de Collectes et Dons de Sang

**BloodLink** est une application web PHP/MySQL dédiée à la gestion des collectes de sang, à la prise de rendez-vous des donneurs, au suivi médical pré-don, à la qualification biologique des poches en laboratoire et à la diffusion d’alertes urgentes **SOS Sang**.

---

## Table des Matières

1. [Overview](#overview)
2. [Features](#features)
3. [Tech Stack](#tech-stack)
4. [Architecture](#architecture)
5. [Getting Started](#getting-started)
6. [Academic Context](#academic-context)
7. [Contributors](#contributors)
8. [Acknowledgments](#acknowledgments)

---

## Overview

Dans les centres de transfusion et lors des campagnes mobiles, la coordination entre l’inscription des donneurs, l’accueil sur place, l’examen médical pré-don, les analyses biologiques en laboratoire et les appels urgents au don nécessite un suivi précis et centralisé.

**BloodLink** connecte trois profils autour d’un parcours médical complet :
- **Donneur** : consultation des collectes avec filtres par ville, plage de dates et plage horaire (format 24h), réservation de créneau, suivi du délai légal de 12 semaines (84 jours) entre deux dons de sang total, accès au QR code personnel, à l’historique des dons et aux résultats validés.
- **Responsable de collecte** : création et gestion des collectes (avec photo de couverture et galerie photos), gestion des créneaux horaires, accueil des donneurs (réservation en ligne, venue sur place ou scan QR), saisie de l’examen médical pré-don, enregistrement de la poche prélevée (`420–480 ml`), saisie des résultats de laboratoire (`VIH`, `VHB`, `VHC`, `Syphilis`, groupe sanguin confirmé) et bilan volumétrique du centre.
- **Administrateur** : tableau de bord analytique des volumes de sang sain qualifié par groupe sanguin, statistiques détaillées par collecte, gestion des comptes et des rôles, invitation des responsables de centre et modération des alertes SOS.

---

## Features

- **Calendrier et réservation de collectes (24h)** :
  - Recherche par ville ou mot-clé, filtrage par plage de dates (`Du / Au`) et par créneau horaire (`De / À` au format 24h).
  - Galerie photos sur la fiche de chaque collecte et réservation en temps réel selon la capacité disponible.
- **Suivi médical, laboratoire et règle des 12 semaines** :
  - Vérification de l’identité du donneur par code QR ou code de réservation à 12 caractères.
  - Saisie des constantes pré-don (tension artérielle, pouls, température, hémoglobine, poids) et décision d’aptitude (`APTE` / `AJOURNE`).
  - Traçabilité des poches prélevées et qualification biologique en laboratoire avant comptabilisation dans les volumes disponibles par groupe sanguin (`O-`, `O+`, `A-`, `A+`, `B-`, `B+`, `AB-`, `AB+`).
  - Calcul automatique du délai de récupération de 12 semaines avant toute nouvelle réservation.
- **Alertes SOS Sang (validité de 2 heures)** :
  - Publication rapide d’appels urgents indiquant l’établissement, la ville, le groupe sanguin recherché, les groupes de globules rouges compatibles et un compte à rebours de 2 heures.
- **Gestion des accès et sécurité** :
  - Espaces dédiés par rôle (`DONNEUR`, `RESPONSABLE`, `ADMIN`), invitation des responsables par lien sécurisé à usage unique, protection CSRF, hachage des mots de passe et expiration automatique de session après inactivité.

---

## Tech Stack

### Frontend
- **HTML5 & CSS3** (`public/assets/css/bloodlink.css`, `public/assets/css/app.css`)
- **JavaScript ES6** (`public/assets/js/validation.js`, `public/assets/js/forms.js`, `public/assets/js/experience.js`)
- **QR Code Generator** (`public/assets/js/vendor/qrcode.js`)

### Backend
- **PHP 8.2+** (Architecture MVC, PDO, sessions sécurisées)
- **MySQL / MariaDB** (Base relationnelle `bloodlink_piweb_2a6`)
- **Apache** (Environnement XAMPP)

---

## Architecture

L’application est structurée selon le patron **Modèle–Vue–Contrôleur (MVC)** :

```text
Esprit-PIWEB-2A6-2026-BloodLink/
├── config/
│   └── config.php                     # Configuration et connexion PDO (Singleton)
├── Model/                             # Entités métier
│   ├── Utilisateur.php
│   ├── CollecteSang.php
│   ├── CreneauCollecte.php
│   ├── ParticipationCollecte.php
│   ├── DonSang.php
│   ├── ExamenPreDon.php
│   ├── ResultatLaboratoire.php
│   ├── AlerteSos.php
│   └── ParametreResponsable.php
├── Controller/                        # Contrôleurs métier
│   ├── AuthController.php
│   ├── CompteController.php
│   ├── UtilisateurController.php
│   ├── InvitationController.php
│   ├── CollecteController.php
│   ├── MedicalController.php
│   ├── SosController.php
│   ├── ParametreController.php
│   └── TableauBordController.php
├── Service/
│   └── Securite.php                   # Sessions, contrôle d'accès, protection CSRF et XSS
├── View/
│   ├── front-office/                  # Vues publiques et espace donneur (avec includes/)
│   └── back-office/                   # Vues administrateur et responsable (avec partials/)
├── public/
│   ├── assets/                        # Feuilles de style CSS, scripts JS et images
│   └── uploads/collectes/             # Photos des collectes
├── database.sql                       # Schéma SQL de la base de données
└── index.php                          # Routeur principal
```

---

## Getting Started

### Prérequis
- **XAMPP** (Apache, MySQL/MariaDB, PHP 8.1+)
- **Git**

### Installation et exécution

1. Cloner le dépôt dans le dossier `htdocs` de XAMPP :
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Bacem-Trimech/Esprit-PIWEB-2A6-2026-BloodLink.git
   ```
2. Démarrer **Apache** et **MySQL** depuis le panneau XAMPP.
3. Ouvrir `http://localhost/phpmyadmin` et importer le fichier `database.sql` (qui crée la base `bloodlink_piweb_2a6` et ses tables).
4. Accéder à l’application dans le navigateur :
   ```text
   http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/
   ```

### Comptes de démonstration
Mot de passe commun aux comptes de démonstration : `abcdefghijklmnop`

| Rôle | Identifiant (E-mail) |
| :--- | :--- |
| **Administrateur** | `bacemtrimech6@gmail.com` |
| **Responsable de collecte** | `responsable.demo@bloodlink.test` |
| **Donneur** | `demo.donneur1@bloodlink.test` |

---

## Academic Context

Ce projet a été développé au sein d’**Esprit School of Engineering** dans le cadre du module **Projet Intégré Technologies Web (PIWEB)** par la classe **2A6** durant l’année universitaire **2026–2027**.

---

## Contributors

- **Bacem Trimech** (`BloodLink`)
- **Khalil** (`AnnuaireMedicale`)
- **Iyed** (`Laboratoires`)
- **Alaa** (`Pharmacies`)
- **Hedia** (`RendezVous`)

---

## Acknowledgments

- L’équipe enseignante du module **PIWEB** à **Esprit School of Engineering** pour l’encadrement académique.
- Documentation médicale de référence : [Organisation Mondiale de la Santé (OMS)](https://iris.who.int/bitstream/handle/10665/76724/9789241548519_eng.pdf?sequence=1) et [Idaraty Tunisie — Don du sang total](https://www.idaraty.tn/fr/public/fr/public/index.php/fr/procedures/don-du-sang-total).
