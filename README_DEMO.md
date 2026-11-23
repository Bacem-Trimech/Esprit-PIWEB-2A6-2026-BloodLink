# BloodLink — Module Services de santé (prototype)

## Technologies et contraintes respectées
- PHP natif avec architecture MVC (Model / Controller / View).
- HTML, CSS et JavaScript.
- XAMPP / Apache ; aucune base MySQL utilisée par ce prototype.
- Aucun fichier JSON.
- Stockage temporaire en sessions PHP (`$_SESSION`).
- Validation JavaScript des formulaires et vérification complémentaire côté PHP.
- Protection des formulaires POST par jeton CSRF.

## Installation avec XAMPP et VS Code
1. Décompresser `BloodLink_Module_Starter_Updated` dans `htdocs`.
2. Ouvrir le dossier dans VS Code.
3. Démarrer Apache dans XAMPP.
4. Visiter `http://localhost/BloodLink_Module_Starter_Updated/`.

## Accès
- Front Office : `http://localhost/BloodLink_Module_Starter_Updated/`
- Connexion Back Office : `http://localhost/BloodLink_Module_Starter_Updated/?action=admin-login`
- Gestionnaire santé : `http://localhost/BloodLink_Module_Starter_Updated/?action=dashboard`
- Compte administrateur de démonstration : `admin` / `BloodLink2026!`

## Fonctionnalités présentes

### EtablissementSante
- Établissements : consultation publique, détails, recherche, ajout, modification et suppression côté administrateur.
- Services médicaux : CRUD administrateur, association à un établissement, consultation publique.
- Ressources médicales : CRUD administrateur, état/quantité, consultation publique.
- Avis : formulaire de dépôt, note sur 5, modération dans le Back Office, affichage des avis publiés.
- Signalements : formulaire de dépôt et traitement administratif (statut et réponse).

### GestionnaireSante
- Sommeil : ajout/modification/suppression, durée calculée depuis les heures.
- Activités sportives : suivi des activités et durées/distances facultatives.
- Médicaments : suivi des instructions saisies, dates et heures de rappel enregistrées.
- Cycle menstruel : suivi facultatif, dates et notes facultatives.
- Habitudes quotidiennes : objectifs et progression.
- Tableau de bord : compteurs des suivis et rappel visuel des horaires saisis.
- Les données personnelles sont filtrées par session PHP dans ce prototype.

## Principales routes
- `?action=admin-index`
- `?action=admin-module&type=services|ressources|avis|signalements`
- `?action=module-form&type=services|ressources|avis|signalements|sommeil|activites|medicaments|cycle|habitudes`
- `?action=module-delete&type=...` (POST)
- `?action=public-module&type=services|ressources|avis`
- `?action=submit-public&type=avis|signalements`
- `?action=dashboard`
- `?action=personal&type=sommeil|activites|medicaments|cycle|habitudes`

## Organisation
- `Model/` : modèles et stockage provisoire en session.
- `Controller/` : contrôleurs des établissements et des autres fonctionnalités.
- `View/front-office/` : pages publiques et espace personnel.
- `View/back-office/` : administration.
- `View/shared/` : vues réutilisées pour les formulaires et listes.
- `public/assets/css/` : styles.
- `public/assets/js/` : contrôles de saisie et interactions.
- `Service/` : réservé aux services communs de l'équipe ; non modifié par ce prototype.

## Limites importantes avant l'intégration d'équipe
- Le stockage en session n'est pas persistant : les données disparaissent à l'expiration de la session. Il sert uniquement à la démonstration sans SQL ni JSON.
- Le compte administrateur est codé en dur pour les essais locaux. Il doit être remplacé par le service d'authentification/autorisation commun de l'équipe.
- Le compte utilisateur est simulé par la session pour démontrer l'espace personnel. Il doit être relié au véritable utilisateur connecté.
- Les rappels de médicaments sont des informations affichées à partir des heures enregistrées ; aucune notification système n'est envoyée. L'application ne prescrit pas et ne modifie pas les instructions médicales.
- Le suivi du cycle est facultatif et les données sont privées dans le cadre de cette démonstration. Il ne fournit aucun diagnostic ni prédiction médicale.
- Avant fusion dans le dépôt commun, comparer les fichiers `index.php`, les contrôleurs et `config/` avec la branche de l'équipe et intégrer le routeur/authentification communs sans écraser le travail des autres.
- Les vérifications JavaScript ne remplacent pas les validations PHP côté serveur.
