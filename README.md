# RendezVous - Module MVC

Structure calquée sur l'organisation du projet exemple ProduitCRUD :
- Controller : opérations CRUD et requêtes PDO.
- Model : classes des deux entités.
- View/BackOffice : pages séparées add/list/edit/delete.
- View/FrontOffice : page d'accueil du module.
- config.php : connexion PDO.
- index.php : redirection vers le FrontOffice.

## Installation
1. Placer le dossier `RendezVous_MVC` dans `C:\xampp\htdocs\`.
2. Démarrer Apache et MySQL.
3. Importer `database.sql` dans phpMyAdmin.
4. Si nécessaire, modifier les paramètres de connexion dans `config.php`.
5. Ouvrir `http://localhost/RendezVous_MVC/`.

## Tables
`Praticien` et `RendezVous`. La table RendezVous contient la clé étrangère `id_praticien`.
La méthode `listRendezVous()` réalise la jointure entre ces deux tables.

## Important
Les pages de suppression sont simplifiées pour le travail CRUD pédagogique. Pour un déploiement réel, remplacer les liens GET de suppression par des formulaires POST protégés par jeton CSRF et ajouter l'authentification/autorisation par rôle demandée dans les consignes.

## Design (BloodLink)
- `assets/css/style.css` : design system (rouge BloodLink, cartes arrondies, dégradé rosé).
- `View/partials/` : icônes SVG, en-têtes/pieds de page FrontOffice (barre du haut) et BackOffice (barre latérale).
- FrontOffice : `index.php` (recherche de praticiens), `rendezvous.php` (liste en cartes).
- BackOffice : `dashboard.php` (statistiques) + pages CRUD.
