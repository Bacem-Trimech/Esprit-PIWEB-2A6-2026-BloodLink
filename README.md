# Esprit-PIWEB-2A6-2026-BloodLink

> **BloodLink** — Plateforme web MVC de gestion des collectes de sang, suivi médical pré-don, qualification biologique en laboratoire, traçabilité des poches et alertes SOS urgentes.  
> Academic project developed at **Esprit School of Engineering** (`Esprit-PIWEB-2A6-2026-BloodLink`, année universitaire 2026–2027).

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-PDO-4479A1?logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC%20%2B%20Singleton-D92735)
![School](https://img.shields.io/badge/Esprit%20School%20of%20Engineering-2A6%20PIWEB-B91C1C)

---

## Table des Matières

1. [Overview](#overview)
2. [Description](#description)
3. [Features](#features)
4. [Fonctionnalités](#fonctionnalités)
5. [Tech Stack](#tech-stack)
   - [Frontend](#frontend)
   - [Backend](#backend)
6. [Architecture](#architecture)
7. [Getting Started](#getting-started)
8. [Installation](#installation)
9. [Utilisation](#utilisation)
10. [Academic Context](#academic-context)
11. [Contributors](#contributors)
12. [Contributions](#contributions)
13. [Acknowledgments](#acknowledgments)
14. [Licence](#licence)

---

## Overview

**BloodLink** is a full-stack PHP/MySQL web application developed using the **Model–View–Controller (MVC)** architecture at **Esprit School of Engineering** as part of the *Projet Intégré Technologies Web (PIWEB)* module for class **2A6** (Academic Year **2026–2027**). It connects blood donors (**Donneurs**), blood drive managers (**Responsables de collecte**), and platform administrators (**Administrateurs**) around a unified, medically traceable workflow: blood drive scheduling, 24-hour slot reservation, QR donor identification, pre-donation medical examination, blood bag collection (`420–480 ml`), biological laboratory validation (`VIH`, `VHB`, `VHC`, `Syphilis`), 12-week inter-donation recovery enforcement, and 2-hour emergency **SOS Sang** alerts.

---

## Description

Dans de nombreux centres de transfusion et campagnes mobiles, la coordination entre la prise de rendez-vous des donneurs, l’accueil sur place, l’examen médical pré-don, la qualification biologique des poches en laboratoire et la diffusion d’appels urgents au don (**SOS Sang**) souffre d’un manque de traçabilité unifiée.

**BloodLink** répond à cette problématique en proposant :
- Un **Front-Office public et donneur** permettant de filtrer les collectes par ville, plage de dates (`Du / Au`) et plage horaire (`De / À` au format 24h), de visualiser la galerie photos du centre, de réserver un créneau, de suivre son délai légal de récupération de **12 semaines (84 jours)** avec compte à rebours, de consulter ses examens et analyses validés, et de publier ou consulter des alertes **SOS Sang** valables 2 heures avec calcul automatique des groupes compatibles en globules rouges.
- Un **Back-Office Responsable de collecte** dédié au pilotage opérationnel d’un centre : création de collectes avec double aperçu photo en direct (couverture + galerie jusqu’à 4 photos), horloge 24h (`00 h : 00` à `23 h : 55`), gestion des créneaux, enregistrement des arrivées (réservation en ligne, venue sur place ou scan QR), saisie de l’examen médical pré-don, confirmation de la poche prélevée (`BL-XXXXXXXXXXXX`), validation sérologique en laboratoire et comptabilisation automatique du sang sain confirmé par groupe sanguin (`A+`, `O+`, etc.) ou en attente de laboratoire.
- Un **Back-Office Administrateur** dédié à la supervision globale : tableau de bord analytique du sang sain qualifié par groupe sanguin, fenêtre modale de détail par collecte (statistiques, traçabilité des poches et état d’éligibilité des donneurs), gestion complète des utilisateurs et des rôles (**CRUD MVC** + modification rapide du rôle en ligne + recherche instantanée), invitation sécurisée des responsables par jeton SHA-256 à usage unique, et modération des alertes SOS.

---

## Features

- **Complete User & Role Management (MVC CRUD)**:
  - Three distinct roles: `ADMIN`, `RESPONSABLE`, and `DONNEUR`.
  - Full user CRUD in the Admin portal, inline role switching, account activation/deactivation preserving medical donation history, and SHA-256 single-use invitation tokens valid for 30 days for onboarding `RESPONSABLE` accounts.
- **Session & Security Hardening (*Workshop Sécurité PHP*)**:
  - Singleton PDO connection (`Database::getConnexion()` / `Config::getConnexion()`) with `ATTR_EMULATE_PREPARES => false` and `ATTR_ERRMODE => ERRMODE_EXCEPTION`.
  - Secure session flags (`session.use_strict_mode`, `HttpOnly`, `SameSite=Lax`, `session_regenerate_id(true)` on authentication and logout) and automatic **30-minute inactivity timeout** (`1800` seconds via `$_SESSION['last_activity']`).
  - Password hashing with `password_hash(..., PASSWORD_DEFAULT)` and verification via `password_verify()`.
  - Anti-CSRF tokens (`random_bytes(32)` + `hash_equals()`) on all state-changing `POST` forms, strict output escaping via `htmlspecialchars()`, and security headers (`Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`).
- **Dual Input Validation (PHP + JavaScript without HTML `required`)**:
  - Every form is validated client-side in `public/assets/js/validation.js` and server-side in `Controller/*.php` without relying on HTML5 `required` attributes.
- **Laboratory-Qualified Blood Analytics & 12-Week Rule**:
  - Blood volume (`ml`) and blood bags are automatically categorized by confirmed blood type (`O-`, `O+`, `A-`, `A+`, `B-`, `B+`, `AB-`, `AB+`) only after laboratory validation (`statut = 'VALIDE'`) with all four screening markers (`VIH`, `VHB`, `VHC`, `Syphilis`) confirmed `NON_REACTIF`. Pending or unconfirmed bags are tracked separately under *Groupe inconnu / Labo en attente*.
  - Enforces the 12-week (84-day) recovery interval between whole blood donations on both online reservations and walk-in registrations.

---

## Fonctionnalités

### 1. Module Gestion des Utilisateurs & Sécurité
- **Authentification & Sessions** : Connexion, déconnexion avec destruction complète du cookie de session, régénération d’ID de session, expiration automatique après 30 minutes d’inactivité.
- **CRUD Utilisateurs & Rôles** : Création de donneurs, invitation de responsables par lien/jeton à usage unique, consultation avec barre de recherche en temps réel, modification rapide du rôle (`DONNEUR`, `RESPONSABLE`, `ADMIN`) directement dans la liste, activation/désactivation.
- **Profils adaptés par rôle** : Profil complet avec préférences et QR personnel pour le donneur ; profil simplifié 2 colonnes (coordonnées + sécurité mot de passe avec icône œil SVG) pour l’administrateur et le responsable.

### 2. Module Collectes, Créneaux & Réservations (Format 24h)
- **Gestion des collectes (Responsable)** : Création et édition avec téléversement d’une photo de couverture (aperçu carte en direct à gauche) et d’une galerie jusqu’à 4 photos (aperçu miniature en direct à gauche), sélecteur d’heure 24h (`00 h : 00` à `23 h : 55`), configuration simplifiée du centre (nom de l’établissement + capacité par défaut des créneaux).
- **Exploration & Filtres (Front-Office)** : Recherche par ville/mot-clé, filtre par plage de dates (`Du / Au`) et filtre par plage horaire en format 24h (`De / À`, ex. `10:00` à `12:00`), galerie interactive à gauche sur la page détail d’une collecte, réservation transactionnelle avec verrouillage `SELECT ... FOR UPDATE`.

### 3. Module Parcours Médical, Laboratoire & Statistiques
- **Accueil & QR Donneur** : Vérification par caméra (`BarcodeDetector`) ou saisie du code QR opaque / code de réservation à 12 caractères, enregistrement des arrivées et des donneurs sur place.
- **Examen Pré-Don & Prélèvement** : Saisie des constantes (tension systolique/diastolique, pouls, température, hémoglobine, poids), décision d’aptitude (`APTE`, `AJOURNE`), confirmation du don (`420–480 ml`) avec génération automatique du numéro de poche.
- **Qualification Biologique & Bilans** : Saisie des résultats laboratoire (`VIH`, `VHB`, `VHC`, `Syphilis`, groupe sanguin confirmé, avis pour le prochain don) et mise à jour instantanée des bilans volumétriques de l’administrateur et du responsable.

### 4. Module Alertes SOS Sang (2 heures)
- Publication publique d’appels urgents sans compte requis (hôpital/lieu, ville, groupe du receveur, téléphone, consentement), affichage automatique des groupes de globules rouges compatibles, compte à rebours en direct de 2 heures et modération par l’administrateur.

---

## Tech Stack

### Frontend
- **HTML5 & CSS3** : Design sur mesure responsive (`public/assets/css/bloodlink.css`, `public/assets/css/app.css`) aux couleurs médicales BloodLink.
- **JavaScript (ES6 Vanilla)** :
  - `public/assets/js/validation.js` : Contrôle de saisie client sur tous les formulaires (sans attribut HTML `required`).
  - `public/assets/js/forms.js` : Soumission fluide `fetch`, notifications toast de sauvegarde, sélecteur d’horloge 24 heures (`00–23h`), bascule de visibilité des mots de passe par icône œil SVG, aperçus d’images en direct (`FileReader`), filtres dynamiques de collectes et de tableaux.
  - `public/assets/js/experience.js` : Compte à rebours SOS 2h, minuteur d’éligibilité de 12 semaines, rendu QR et lecteur caméra.
  - `public/assets/js/vendor/qrcode.js` : Génération locale de QR Code (`qrcode-generator`, licence MIT).

### Backend
- **PHP 8.2+** : Architecture **MVC** stricte, typage strict (`declare(strict_types=1)`), sessions sécurisées, PDO préparé.
- **MySQL / MariaDB** : Base relationnelle `bloodlink_piweb_2a6` avec contraintes d’intégrité référentielle (`FOREIGN KEY`), transactions ACID et indexation.
- **Apache (XAMPP)** : Serveur HTTP local.

---

## Architecture

Le projet respecte rigoureusement le patron **Modèle–Vue–Contrôleur (MVC)** ainsi que la séparation modulaire des gabarits (`includes/` pour le Front-Office et `partials/` pour le Back-Office) :

```text
Esprit-PIWEB-2A6-2026-BloodLink/
├── config/
│   └── config.php                     # Singleton Database::getConnexion() / Config::getConnexion() (PDO)
├── Model/                             # 9 classes d'entités métier (propriétés privées, getters/setters)
│   ├── Utilisateur.php
│   ├── CollecteSang.php
│   ├── CreneauCollecte.php
│   ├── ParticipationCollecte.php
│   ├── DonSang.php
│   ├── ExamenPreDon.php
│   ├── ResultatLaboratoire.php
│   ├── AlerteSos.php
│   └── ParametreResponsable.php
├── Controller/                        # Contrôleurs métier (logique, validation PHP & requêtes PDO)
│   ├── AuthController.php
│   ├── CompteController.php
│   ├── UtilisateurController.php
│   ├── CollecteController.php
│   ├── SuiviMedicalController.php
│   ├── SosController.php
│   ├── ParametreController.php
│   └── TableauBordController.php
├── Service/
│   └── Securite.php                   # Sessions (timeout 30 min), CSRF, RBAC, headers HTTP, échappement XSS
├── View/
│   ├── front-office/                  # Gabarit et vues publiques / espace donneur
│   │   ├── includes/                  # Composants modulaires Front-Office (head, navbar, footer, scripts)
│   │   ├── layout.php
│   │   ├── page.php
│   │   └── extra.php
│   └── back-office/                   # Gabarit et vues d'administration / espace responsable
│       ├── partials/                  # Composants modulaires Back-Office (head, sidebar, header, footer, scripts)
│       ├── layout.php
│       ├── page.php
│       ├── extra.php
│       └── collecte-gestion.php
├── public/
│   ├── assets/                        # Feuilles de style CSS, scripts JS et illustrations SVG
│   └── uploads/collectes/             # Photos téléversées pour les collectes
├── database.sql                       # Script SQL complet de création de la base et des tables
├── index.php                          # Routeur frontal unique (Front Controller)
└── README.md                          # Documentation standardisée du projet
```

---

## Getting Started

Suivez les étapes ci-dessous pour installer, configurer et lancer **BloodLink** sur un environnement local **XAMPP**.

### Prérequis
- **XAMPP** (PHP `8.1` ou `8.2+`, MySQL/MariaDB, Apache)
- **Git**
- Un navigateur web moderne (Chrome, Edge, Firefox)

---

## Installation

1. **Cloner le dépôt GitHub dans le répertoire `htdocs` de XAMPP** :
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Bacem-Trimech/Esprit-PIWEB-2A6-2026-BloodLink.git
   cd Esprit-PIWEB-2A6-2026-BloodLink
   ```

2. **Démarrer les services Apache et MySQL** depuis le panneau de contrôle XAMPP.

3. **Importer la base de données** :
   - Ouvrir `http://localhost/phpmyadmin`
   - Importer le fichier `database.sql` situé à la racine du projet (il crée automatiquement la base `bloodlink_piweb_2a6` et l’ensemble des tables).

4. **Vérifier la configuration PDO** dans `config/config.php` :
   - Hôte par défaut : `127.0.0.1`
   - Base de données : `bloodlink_piweb_2a6`
   - Utilisateur : `root` (mot de passe vide par défaut sur XAMPP)
   - Ces paramètres peuvent également être surchargés via les variables d’environnement `BLOODLINK_DB_HOST`, `BLOODLINK_DB_NAME`, `BLOODLINK_DB_USER` et `BLOODLINK_DB_PASS`.

---

## Utilisation

Ouvrir l’application dans le navigateur :
```text
http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/
```
*(ou sur le port configuré pour Apache, par exemple `http://127.0.0.1:8088/Esprit-PIWEB-2A6-2026-BloodLink/`).*

### Comptes de démonstration préconfigurés
Tous les comptes de démonstration utilisent le mot de passe : `abcdefghijklmnop`

| Rôle | E-mail de connexion | Espace principal |
| :--- | :--- | :--- |
| **Administrateur** | `bacemtrimech6@gmail.com` | `index.php?page=tableau-bord` |
| **Responsable de collecte** | `responsable.demo@bloodlink.test` | `index.php?page=gestion-collectes` |
| **Donneur 1** | `demo.donneur1@bloodlink.test` | `index.php?page=collectes` |
| **Donneur 2** | `demo.donneur2@bloodlink.test` | `index.php?page=collectes` |
| **Donneur 3** | `demo.donneur3@bloodlink.test` | `index.php?page=collectes` |

---

## Academic Context

This repository is an academic engineering project developed at **Esprit School of Engineering** (École Supérieure Privée d'Ingénierie et de Technologies, Tunis, Tunisia) during the **2026–2027** academic year.

- **Institution**: **Esprit School of Engineering**
- **Module**: Projet Intégré Technologies Web (**PIWEB**)
- **Class**: **2A6**
- **Repository Naming Standard**: `Esprit-PIWEB-2A6-2026-BloodLink`
- **Recommended GitHub Topics**: `esprit-school-of-engineering`, `academic-project`, `esprit-piweb`, `2026-2027`, `php-mvc`, `mysql`, `blood-donation`

---

## Contributors

Projet réalisé en travail collaboratif au sein de la classe **2A6** à **Esprit School of Engineering** :

- **Bacem Trimech** — Branche `BloodLink-Bacem` (Gestion des utilisateurs, sécurité & sessions, collectes de sang, suivi médical & laboratoire, alertes SOS et tableaux de bord analytiques)
- **Khalil** — Branche `AnnuaireMedicale-Khalil`
- **Iyed** — Branche `Laboratoires-Iyed`
- **Alaa** — Branche `Pharmacies-Alaa`
- **Hedia** — Branche `RendezVous-Hedia`

---

## Contributions

Le développement suit un flux collaboratif Git structuré :
1. Chaque membre développe son module sur sa branche dédiée et suit ses tâches dans le **GitHub Project Board** associé au dépôt.
2. Les modifications sont vérifiées (`php -l`), commitées avec des messages explicites et fusionnées pour l’intégration finale sur la machine de validation du groupe.

---

## Acknowledgments

- L’équipe pédagogique et les tuteurs du module **PIWEB** à **Esprit School of Engineering** pour leurs guides d’architecture MVC, d’intégration de templates, de sécurité des sessions PHP et de standardisation GitHub.
- Références médicales consultées pour les règles de sécurité transfusionnelle (intervalle de 12 semaines et volume de 420–480 ml) : [Organisation Mondiale de la Santé (OMS)](https://iris.who.int/bitstream/handle/10665/76724/9789241548519_eng.pdf?sequence=1) et [Idaraty Tunisie — Don du sang total](https://www.idaraty.tn/fr/public/fr/public/index.php/fr/procedures/don-du-sang-total).

---

## Licence

Projet académique développé à **Esprit School of Engineering** (2026–2027). Le générateur QR embarqué (`public/assets/js/vendor/qrcode.js` par Kazuhiko Arase) est distribué sous licence **MIT**.
