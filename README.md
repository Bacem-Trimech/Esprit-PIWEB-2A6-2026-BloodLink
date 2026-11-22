# BloodLink — Projet Web 2A6

Application de gestion des collectes de sang, des réservations, des arrivées sur place et de l’historique des dons. Interface entièrement en français.

## Fonctionnalités

- Compte donneur : inscription, connexion, déconnexion, consultation des collectes, réservation et annulation d’un créneau, historique des réservations et des dons confirmés.
- Responsable : création et publication des collectes, création des créneaux, accueil des personnes sans réservation, création avec consentement d’un compte à activer par le donneur, suivi des participations et confirmation des dons.
- Administrateur : consultation, ajout, modification, désactivation et suppression des comptes ; attribution des rôles.
- Sécurité : mots de passe hachés, requêtes SQL préparées, contrôle des rôles à chaque requête, protection CSRF, échappement HTML, renouvellement de l’identifiant de session, cookie HttpOnly/SameSite, validation PHP et JavaScript.

## Architecture MVC

- `Model/` : objets représentant les données.
- `Controller/` : traitements et requêtes préparées.
- `View/front-office/` et `View/back-office/` : affichage public et administration.
- `Service/` : sécurité des sessions et fonctions communes.
- `config/` : connexion PDO.
- `public/assets/` : styles et JavaScript. Le thème reprend les couleurs et composants de BloodLink-Atelier.
- `index.php` : routage.

## Installation sur XAMPP

1. Placer le dépôt dans `C:\xampp\htdocs\Esprit-PIWEB-2A6-2026-BloodLink`.
2. Démarrer Apache et MySQL dans XAMPP.
3. Importer `database.sql` dans phpMyAdmin, ou lancer :
   `C:\xampp\mysql\bin\mysql.exe -u root < database.sql`
4. Par défaut, la base s’appelle `bloodlink_piweb_2a6`, avec `root` et un mot de passe vide. Pour modifier cette configuration, définir `BLOODLINK_DB_HOST`, `BLOODLINK_DB_NAME`, `BLOODLINK_DB_USER` et `BLOODLINK_DB_PASS` dans l’environnement du serveur.
5. Créer le premier administrateur depuis un terminal local : définir temporairement `BLOODLINK_ADMIN_PASSWORD` avec un mot de passe d’au moins 12 caractères, puis lancer `C:\xampp\php\php.exe scripts/creer_admin.php "Nom Administrateur" admin@example.org`. Effacer ensuite cette variable d’environnement.
6. Ouvrir `http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/`.

Les mots de passe ne doivent jamais être placés dans le dépôt. L’inscription publique crée uniquement des comptes `DONNEUR`. Les rôles élevés sont attribués dans l’espace administrateur.

## Travail en groupe

Les branches des membres servent aux développements individuels. Les tâches et leur responsable doivent être suivis dans le GitHub Project Board du dépôt. Chaque contribution passe par des commits traçables et une fusion contrôlée dans `main`. Avant la validation, importer la base et vérifier les fonctionnalités de chaque membre sur la même machine.
