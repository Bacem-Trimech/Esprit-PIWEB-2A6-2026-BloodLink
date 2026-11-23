# Projet Laboratoire (MVC PHP)

Base de données : **2 tables seulement**
- `analyse_medicale` : le catalogue des analyses (nom, type d'échantillon, prix, délai, à jeun)
- `compte_rendu_analyse` : le résultat d'une analyse pour un patient (`id_analyse` = clé étrangère vers `analyse_medicale`)

## Installation
1. Copier le dossier `laboratoire` dans `htdocs` (XAMPP).
2. Importer `gslaboratoire.sql` dans phpMyAdmin.
3. Vérifier `config.php` (user `root`, mot de passe vide, base `gslaboratoire`).
4. Dashboard (point d'entrée) : http://localhost/laboratoire/view/backoffice/dashboard.php
5. Front office : http://localhost/laboratoire/view/frontoffice/consulterCompteRendu.php

## Structure
- `model/` : classes AnalyseMedicale et CompteRenduAnalyse
- `controller/` : AnalyseMedicaleC et CompteRenduAnalyseC (CRUD + contrôle de saisie + affichage)
- `view/backoffice/` : dashboard, listes, ajout, modification, suppression + contrôle JS
- `view/frontoffice/` : recherche par patient et affichage d'un compte rendu
- `view/partials/` : sidebar/en-tête/pied de page communs (style BloodLink)
- `view/assets/style.css` : thème rouge/rose BloodLink

## Fonctionnalité métier
`CompteRenduAnalyseC::afficherCompteRendu($id)` joint `compte_rendu_analyse` et
`analyse_medicale` pour afficher le compte rendu avec le nom de l'analyse correspondante
(`view/frontoffice/voirCompteRendu.php`). La liste des comptes rendus affiche aussi ce nom.
