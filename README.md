# Esprit-PIWEB-2A6-2026-BloodLink

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC%20Intégré-D92735?style=flat-square)
![Esprit](https://img.shields.io/badge/Esprit%20School%20of%20Engineering-2A6%20PIWEB-B91C1C?style=flat-square)

Plateforme web médicale **PHP / MySQL (MVC)** intégrant dans une seule application et une seule base de données (`bloodlink_piweb_2a6`) les **5 modules** du projet de groupe : **BloodLink (Dons & SOS)**, **Annuaire Médical**, **Rendez-vous**, **Pharmacies de garde** et **Laboratoires d’analyses**.

---

## Overview

| Module (Branche Git fusionnée) | Entités & Liaisons | Fonctionnalités principales |
| :--- | :--- | :--- |
| **`BloodLink`** | `Utilisateur`, `CollecteSang`, `CreneauCollecte`, `ParticipationCollecte`, `ExamenPreDon`, `DonSang`, `ResultatLaboratoire`, `AlerteSos`, `ParametreResponsable` | Collectes de sang avec filtres dates/24h et galeries photos, réservations, QR donneur, examen pré-don, prélèvement par poches (`450 ml`), qualification labo, délai 12 semaines et alertes SOS 2h reliées à l’annuaire. |
| **`AnnuaireMedicale`** | `EtablissementSante`, `ServiceMedical` | Répertoire unifié des hôpitaux, cliniques, pharmacies et laboratoires relié aux services médicaux, praticiens, stocks de pharmacies, analyses et collectes par ville. |
| **`RendezVous`** | `Praticien`, `RendezVous` | Liste des praticiens rattachés aux établissements de l’annuaire, prise de rendez-vous en ligne (24h, anti-doublon) liée au compte utilisateur, suivi et validation administrateur. |
| **`Pharmacies`** | `Medicament`, `GardePharmacie`, `Disponibilite` | Pharmacies de garde du jour (table partagée `etablissements_sante`), recherche de médicaments et stocks en temps réel par ville. |
| **`Laboratoires`** | `AnalyseMedicale`, `CompteRenduAnalyse` | Catalogue des analyses biologiques relié aux laboratoires (tarifs, délais, à jeun) et comptes rendus patients rattachés aux comptes utilisateurs. |

---

## Features

- **Gestion complète des utilisateurs & sécurité** — Un seul compte et une seule session PHP sécurisée (`use_strict_mode`, `HttpOnly`, `SameSite=Lax`, expiration 30 min, `session_regenerate_id(true)`, `password_hash`/`password_verify`, CSRF `hash_equals`, échappement XSS, en-têtes HTTP) partagés par les 5 modules, avec contrôle d’accès par rôle (`DONNEUR`, `RESPONSABLE`, `ADMIN`).
- **Validation PHP + JavaScript** — Tous les formulaires Front-Office et Back-Office des 5 modules disposent d’un contrôle de saisie côté client (`public/assets/js/validation.js`) et côté serveur (`Controller/*.php`).
- **Intégration réelle (Liaisons inter-modules)** — Une fiche établissement de l’**Annuaire** affiche ses services, ses praticiens (lien direct *Prendre RDV*), ses gardes/stocks s’il s’agit d’une pharmacie, ses analyses s’il s’agit d’un laboratoire, ainsi que les collectes **BloodLink** de la même ville.

---

## Tech Stack

### Frontend
- **HTML5 / CSS3** — `public/assets/css/bloodlink.css`, `public/assets/css/app.css`
- **JavaScript ES6** — `validation.js`, `forms.js`, `experience.js`, `qrcode.js`

### Backend
- **PHP 8.2+** — Architecture MVC stricte, Singleton PDO (`Database::getConnexion()` / `Config::getConnexion()`), sessions sécurisées
- **MySQL / MariaDB** — Base unifiée `bloodlink_piweb_2a6` (`database/database.sql`)

---

## Architecture

```text
Esprit-PIWEB-2A6-2026-BloodLink/
├── config/config.php          # Connexion PDO Singleton & initialisation unifiée
├── Model/                     # 18 entités MVC couvrant les 5 modules
├── Controller/                # 13 contrôleurs métier (Auth, Users, BloodLink, Annuaire, RDV, Pharmacies, Labo)
├── Service/                   # Securite.php (sessions/CSRF/RBAC) & PharmacieService.php
├── View/
│   ├── front-office/          # includes/ (head, navbar, footer, scripts) + pages publiques & espace utilisateur
│   └── back-office/           # partials/ (head, sidebar, header, footer, scripts) + administration des 5 modules
├── database/database.sql      # Script SQL complet de la base unifiée
├── public/                    # Assets CSS, JS, images et uploads
└── index.php                  # Routeur frontal unique
```

---

## Getting Started

1. **Cloner le dépôt** dans `C:\xampp\htdocs` :
   ```bash
   git clone https://github.com/Bacem-Trimech/Esprit-PIWEB-2A6-2026-BloodLink.git
   ```
2. **Démarrer Apache et MySQL** dans XAMPP.
3. **Importer `database/database.sql`** dans `http://localhost/phpmyadmin`.
4. **Ouvrir l’application** : `http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/`

---

## Academic Context

Projet développé à **Esprit School of Engineering** dans le cadre du module **Projet Intégré Technologies Web (PIWEB)** — Classe **2A6** (Année universitaire **2026–2027**).

## Contributors

- **Bacem Trimech** — `BloodLink` & Socle Utilisateurs / Sécurité / Intégration
- **Khalil Talbi** — `AnnuaireMedicale`
- **Hedia Ben Nacer** — `RendezVous`
- **Aala Werchfani** — `Pharmacies`
- **Iyed Khlif** — `Laboratoires`

## Acknowledgments

- Équipe pédagogique et tuteurs du module **PIWEB** à **Esprit School of Engineering**.
