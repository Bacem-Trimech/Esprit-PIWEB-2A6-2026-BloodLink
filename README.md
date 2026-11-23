# Esprit-PIWEB-2A6-2026-BloodLink

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC-D92735?style=flat-square)
![Esprit](https://img.shields.io/badge/Esprit%20School%20of%20Engineering-2A6%20PIWEB-B91C1C?style=flat-square)

Plateforme web médicale **PHP / MySQL (MVC)** regroupant la gestion des collectes de sang (**BloodLink**), l’annuaire des établissements de santé, la prise de rendez-vous médicaux, les pharmacies et les laboratoires d’analyses.

---

## Overview

| Module (Branche Git) | Description |
| :--- | :--- |
| **`BloodLink`** | Planification des collectes de sang, réservation de créneaux 24h, suivi médical pré-don, qualification laboratoire et alertes SOS 2h. |
| **`AnnuaireMedicale`** | Répertoire des établissements de santé (hôpitaux, cliniques) et de leurs services médicaux internes. |
| **`RendezVous`** | Consultation des praticiens par spécialité et prise de rendez-vous en ligne. |
| **`Pharmacies`** | Localisation des pharmacies, horaires de garde et disponibilité des médicaments. |
| **`Laboratoires`** | Catalogue des laboratoires d’analyses médicales, examens biologiques et tarifs. |

---

## Features

- **Gestion des Utilisateurs & Rôles** — Authentification, sessions sécurisées et contrôle d’accès par rôle (`DONNEUR` / `PATIENT`, `RESPONSABLE`, `ADMIN`).
- **Front-Office & Back-Office** — Interface publique responsive et portail d’administration/gestion modulaire.
- **Validation & Sécurité** — Contrôle de saisie client (JavaScript) et serveur (PHP), requêtes préparées PDO et protection CSRF/XSS.

---

## Tech Stack

### Frontend
- **HTML5 / CSS3** — `public/assets/css/`
- **JavaScript ES6** — `public/assets/js/`

### Backend
- **PHP 8.2+** — Architecture MVC, PDO
- **MySQL / MariaDB** — Base de données relationnelle
- **Apache** — Environnement XAMPP

---

## Architecture

```text
Esprit-PIWEB-2A6-2026-BloodLink/
├── config/config.php          # Connexion à la base de données avec PDO
├── Model/                     # Classes et entités métier
├── Controller/                # Contrôleurs de l'application
├── Service/                   # Services et sécurité des sessions
├── View/
│   ├── front-office/          # Gabarits et pages destinées aux utilisateurs
│   └── back-office/           # Gabarits et pages destinées à l'administration
├── public/assets/             # Fichiers CSS, JavaScript et médias
└── index.php                  # Routeur principal
```

---

## Getting Started

1. **Cloner le dépôt** dans `C:\xampp\htdocs` :
   ```bash
   git clone https://github.com/Bacem-Trimech/Esprit-PIWEB-2A6-2026-BloodLink.git
   ```
2. **Démarrer Apache et MySQL** depuis XAMPP.
3. **Importer la base de données** dans `http://localhost/phpmyadmin`.
4. **Ouvrir l’application** : `http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/`

---

## Academic Context

Projet développé à **Esprit School of Engineering** dans le cadre du module **Projet Intégré Technologies Web (PIWEB)** — Classe **2A6** (Année universitaire **2026–2027**).

## Contributors

Développement collaboratif par l’équipe **2A6** autour des branches :
- `BloodLink`
- `AnnuaireMedicale`
- `RendezVous`
- `Pharmacies`
- `Laboratoires`

## Acknowledgments

- Équipe pédagogique et tuteurs du module **PIWEB** à **Esprit School of Engineering**.
