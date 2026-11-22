# BloodLink — Projet Web 2A6

Application PHP/MySQL de collecte de sang. Interface en français, architecture MVC, thème rouge et blanc repris de BloodLink-Atelier.

## Espaces et parcours

- **Visiteur** : consulter les collectes et les alertes SOS ; publier une alerte sans compte. Elle exige un lieu, une ville, un groupe receveur, un téléphone et le consentement de publication. Elle disparaît automatiquement après 2 heures ; l’administrateur peut la retirer avant. Le tableau des groupes proposés concerne uniquement les globules rouges et ne remplace jamais les contrôles de compatibilité du centre.
- **Donneur** : inscription, connexion, profil (coordonnées, date de naissance, groupe déclaré, couleur de l’avatar, préférence de contact), modification du mot de passe, QR personnel renouvelable, réservation selon les places disponibles, annulation, historique des dons confirmés avec volume et poche, examen et résultats validés.
- **Responsable** : compte créé par invitation d’un administrateur. Il complète ses informations et choisit son mot de passe. Il gère ses collectes et créneaux, enregistre les arrivées et les donneurs sans réservation, crée un compte provisoire pour un visiteur sans compte après consentement et remet un lien ou QR d’activation. Il saisit les mesures avant don, la décision du professionnel, le volume prélevé, le compte rendu du laboratoire et l’avis du centre pour un prochain don. Sa page de paramètres règle son organisation, son contact, la capacité proposée, l’horizon de réservation et le message de ses collectes.
- **Administrateur** : tableau de bord (donneurs, responsables, collectes, réservations, visites sur place, dons, volume, SOS, pages vues), utilisateurs et rôles, invitations responsables, toutes les collectes, modération SOS et identification QR.

Les invitations sont aléatoires, stockées sous forme d’empreinte, à usage unique et valables 30 jours. Une nouvelle invitation remplace l’ancienne. Le QR donneur contient seulement un identifiant opaque ; la consultation exige une connexion responsable ou administrateur. La caméra QR utilise `BarcodeDetector` si le navigateur le permet ; la saisie du code reste possible.

Un compte créé directement reçoit une alerte l’invitant à compléter son profil. La réservation exige un profil donneur complété. La suppression depuis l’administration désactive un compte pour conserver la traçabilité de ses dons.

## Installation XAMPP

1. Cloner ce dépôt dans `C:\xampp\htdocs\Esprit-PIWEB-2A6-2026-BloodLink` et démarrer Apache et MySQL.
2. Pour une **installation neuve**, importer `database.sql` dans phpMyAdmin. Ce fichier crée la base `bloodlink_piweb_2a6` et toutes les tables.
3. Pour une **installation déjà créée avec l’ancien schéma**, exécuter une seule fois les migrations `002_fonctionnalites.sql`, `003_depistages.sql` et `004_responsable_suivi.sql`, dans cet ordre. Si les deux premières ont déjà été appliquées, exécuter seulement `004_responsable_suivi.sql`. Ne pas réimporter `database.sql` sur une base existante.
4. Par défaut, PDO utilise `127.0.0.1`, la base `bloodlink_piweb_2a6`, `root` et un mot de passe vide. Au besoin, définir `BLOODLINK_DB_HOST`, `BLOODLINK_DB_NAME`, `BLOODLINK_DB_USER`, `BLOODLINK_DB_PASS` dans l’environnement du serveur.
5. Créer le premier administrateur, si aucun n’existe : définir temporairement `BLOODLINK_ADMIN_PASSWORD` (12 caractères minimum), puis lancer `C:\xampp\php\php.exe scripts/creer_admin.php "Nom Administrateur" admin@example.org`. Retirer ensuite cette variable. Sur la base locale préparée pour ce projet, le compte Bacem Trimech est déjà administrateur.
6. Ouvrir `http://localhost/Esprit-PIWEB-2A6-2026-BloodLink/`.

L’inscription publique crée seulement un **DONNEUR**. Inviter un responsable depuis « Inviter un responsable » dans l’administration et lui transmettre le lien personnel. Le projet ne fournit pas encore d’e-mail transactionnel : le lien doit être remis par un canal de confiance. Pour scanner avec un téléphone, servir l’application sur une adresse accessible au téléphone et en HTTPS ; `localhost` sur le téléphone désigne le téléphone lui-même.

## Sécurité et portée médicale

Validation des formulaires en PHP et JavaScript (sans attribut HTML `required`), requêtes PDO préparées, échappement des vues, jetons CSRF, mots de passe hachés, renouvellement de session, cookies HttpOnly/SameSite, contrôle des rôles et des collectes à chaque requête, limitation de fréquence SOS. Les résultats du laboratoire ne sont montrés au donneur qu’après validation ; un résultat à confirmer affiche une demande de contact avec le centre, sans détail sensible. La saisie des examens et dépistages doit être effectuée d’après un professionnel et un compte rendu réel. Cette application scolaire ne décide pas de l’aptitude médicale ni de la compatibilité transfusionnelle.

Le volume enregistré pour un don de sang total est limité à 420–480 ml et est saisi comme volume réellement prélevé. Le responsable règle les paramètres de ses propres collectes. L’avis pour un prochain don est saisi manuellement : en cours, autorisé à partir d’une date, ou non autorisé. Même après un avis favorable, une nouvelle évaluation au centre reste nécessaire.

Le QR est généré localement avec `qrcode-generator` de Kazuhiko Arase, licence MIT (`public/assets/js/vendor/qrcode.js`).

## Structure et collaboration

`Controller/` : logique métier ; `Service/` : sécurité ; `View/front-office/` et `View/back-office/` : pages ; `public/assets/` : thème et scripts ; `config/` : PDO ; `database.sql` et `migrations/` : schéma ; `index.php` : routage.

Chaque membre travaille sur sa branche et ses tâches sont suivies dans le GitHub Project Board. Les commits et fusions doivent permettre de retracer les contributions. Avant la validation, réunir et tester toutes les branches sur la machine de démonstration.
