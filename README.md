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

- **Gestion des Utilisateurs & Rôles** — Authentification, sessions sécurisées et contrôle d’accès par rôle (`donneur`, `support`, `moderateur`, `admin_gestion`, `admin_supervision`, `super_admin`).
- **Front-Office & Back-Office** — Interface publique responsive (mode jour / nuit) et portail d’administration/gestion modulaire.
- **Validation & Sécurité** — Contrôle de saisie client (JavaScript) et serveur (PHP), requêtes préparées PDO et protection CSRF/XSS.

### Module Pharmacies (cette branche)

- **Entités propres** : `Medicament` et `GardePharmacie` (CRUD complet en back-office).
- **Entités liées** : `EtablissementSante` (version minimale, appartient au module AnnuaireMedicale) et `Disponibilite` (stock déclaré par une pharmacie pour un médicament).
- **Exemple de JOIN** : trouver une pharmacie **de garde aujourd’hui** qui **déclare un médicament en stock** (`etablissements_sante` + `gardes_pharmacie` + `disponibilites` + `medicaments`), voir `PharmacieService::searchStock()`.
- **Front-office** : page « Pharmacies » (recherche par médicament, ville, pharmacies de garde) et catalogue des médicaments.
- **Back-office** : gestion des médicaments, des périodes de garde (sans chevauchement) et du stock.

### Rôles et permissions

| Rôle | Espace | Utilisateurs | Pharmacies |
| :--- | :--- | :--- | :--- |
| `donneur` | Front-office | Modifier son profil | Consulter |
| `support` | Back-office | Voir, modifier les donneurs, suspendre | Lecture |
| `moderateur` | Back-office | — | Gérer médicaments, gardes, stock |
| `admin_gestion` | Back-office | CRUD donneurs, supports, modérateurs | Gérer |
| `admin_supervision` | Back-office | Lecture seule | Lecture |
| `super_admin` | Back-office | Tout, y compris les rôles | Gérer |

Les permissions sont dans `config/config.php` ; le rôle est stocké en base et relu à chaque requête.

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
├── config/config.php          # Paramètres, connexion à la base de données avec PDO (Singleton), fonctions d'affichage
├── Model/                     # Classes et entités métier (attributs, constructeur, getters/setters)
├── Controller/                # Contrôleurs de l'application
├── Service/                   # Services (SQL, sessions, rôles, CSRF, validation)
├── View/
│   ├── front-office/          # Gabarits et pages destinées aux utilisateurs (includes/)
│   └── back-office/           # Gabarits et pages destinées à l'administration (partials/)
├── database/                  # Scripts SQL de la base de données
├── public/assets/             # Fichiers CSS, JavaScript et médias
└── index.php                  # Routeur principal (index.php?action=...)
```

Note : `head`, `spinner`, `footer` et `scripts` sont partagés par les deux espaces et se trouvent dans `View/front-office/includes/`.

---

## Getting Started

1. **Cloner le dépôt** dans `C:\xampp\htdocs` :
   ```bash
   git clone https://github.com/Bacem-Trimech/Esprit-PIWEB-2A6-2026-BloodLink.git
   ```
2. **Démarrer Apache et MySQL** depuis XAMPP.
3. **Importer la base de données** : dans `http://localhost/phpmyadmin`, onglet **Importer**, choisir `database/bloodlink.sql`.
4. Si besoin, adapter `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` dans `config/config.php`.
5. **Ouvrir l’application** : `http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/`

### Mode démo

`DEMO_MODE` (dans `config/config.php`) affiche sous le formulaire de connexion des boutons par rôle qui remplissent
l’email et le mot de passe. Les comptes de démonstration (`donor@demo.tn`, `support@demo.tn`, `moderateur@demo.tn`,
`gestion@demo.tn`, `supervision@demo.tn`, `super@demo.tn`) ont le mot de passe `User@1234` ; il est haché au premier chargement.
**Mettre `DEMO_MODE` à `false` pour un vrai déploiement.**

---

## Tests à faire

1. **Connexion par rôle** : cliquer sur chaque bouton de rôle puis « Log in » ; chaque rôle arrive dans son espace.
2. **Accès refusé (403)** : connecté en donneur, ouvrir `index.php?action=admin` ; connecté en support, ouvrir `index.php?action=med_create`.
3. **Recherche pharmacies** : page Pharmacies, taper `Doliprane`, laisser « On duty today » coché ; décocher pour voir toutes les pharmacies.
4. **CRUD médicaments** (admin gestion) : ajouter, modifier, supprimer ; tester un doublon et un champ vide (messages d’erreur).
5. **Gardes** : créer une garde qui chevauche une existante → refusée ; date de fin avant la date de début → refusée.
6. **Stock** : mettre la quantité d’un médicament à 0 puis refaire la recherche : la pharmacie disparaît.
7. **Sécurité** : email `' OR '1'='1` (échec), nom d’utilisateur `<script>` (refusé), supprimer le jeton CSRF d’un formulaire (refusé).
8. **Mode nuit** : cliquer sur le cœur du logo.

---

## Security

Requêtes préparées PDO, `password_hash()` / `password_verify()`, `htmlspecialchars()` sur toute sortie, jeton CSRF sur
tous les formulaires POST (déconnexion et suppressions comprises), `session_regenerate_id(true)` à la connexion, cookie
HttpOnly + SameSite, expiration après 30 minutes, en-têtes `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Content-Security-Policy`.

---

## Academic Context

Projet développé à **Esprit School of Engineering** dans le cadre du module **Projet Intégré Technologies Web (PIWEB)** — Classe **2A6** (Année universitaire **2026–2027**).
