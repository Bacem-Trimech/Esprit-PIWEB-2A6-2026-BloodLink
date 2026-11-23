# BloodLink — Conception du module Services de santé

## 1. Périmètre fonctionnel

### EtablissementSante
Une entité fonctionnelle qui regroupe cinq groupes de fonctionnalités :
1. Gestion des établissements.
2. Gestion des services médicaux.
3. Gestion des ressources médicales.
4. Gestion des avis.
5. Gestion des signalements.

### GestionnaireSante
Un espace personnel qui regroupe six groupes de fonctionnalités :
1. Gestion du sommeil.
2. Gestion des activités sportives.
3. Gestion des médicaments et rappels saisis.
4. Suivi facultatif du cycle menstruel.
5. Gestion des habitudes quotidiennes.
6. Tableau de bord, historique et statistiques.

## 2. Acteurs
- Visiteur : consultation des établissements, services et ressources publiés.
- Utilisateur connecté : consultation, dépôt d'avis et signalements, gestion de ses propres suivis personnels.
- Administrateur autorisé : gestion des établissements, services et ressources ; modération des avis ; traitement des signalements.

## 3. Règles de gestion essentielles
- Seul un administrateur autorisé gère les établissements, services et ressources.
- Chaque service et ressource est rattaché à un établissement.
- Un utilisateur ne modifie et ne supprime que ses propres avis et données personnelles.
- Les avis sont soumis à modération avant publication.
- Les signalements possèdent un statut et peuvent recevoir une réponse administrative.
- Les données du GestionnaireSante sont privées.
- Les statistiques sont calculées à partir des enregistrements saisis.
- Le suivi du cycle est facultatif.
- Les rappels de médicaments reproduisent les instructions enregistrées et ne constituent pas une prescription.

## 4. Diagramme de cas d'utilisation — vue synthétique

```mermaid
flowchart LR
  V[Visiteur] --> C1((Consulter établissements))
  V --> C2((Consulter services et ressources))
  U[Utilisateur connecté] --> A1((Déposer un avis))
  U --> A2((Envoyer un signalement))
  U --> G1((Gérer ses suivis personnels))
  U --> G2((Consulter tableau de bord))
  AD[Administrateur] --> M1((Gérer établissements))
  AD --> M2((Gérer services et ressources))
  AD --> M3((Modérer avis))
  AD --> M4((Traiter signalements))
```

## 5. Diagramme de classes — vue conceptuelle

```mermaid
classDiagram
 class EtablissementSante {
   +idEtablissement
   +nom
   +type
   +description
   +adresse
   +ville
   +telephone
   +email
   +horaires
   +latitude
   +longitude
   +accessibilite
   +statutPublication
 }
 class ServiceMedical {
   +idService
   +nomService
   +specialite
   +description
   +horaires
   +statut
 }
 class RessourceMedicale {
   +idRessource
   +nomRessource
   +typeRessource
   +description
   +quantite
   +etat
 }
 class Avis {
   +idAvis
   +note
   +commentaire
   +datePublication
   +statut
 }
 class Signalement {
   +idSignalement
   +motif
   +description
   +dateSignalement
   +statut
   +reponseAdmin
 }
 class GestionnaireSante {
   +idUtilisateur
 }
 class SuiviSommeil {
   +date
   +heureCoucher
   +heureReveil
   +duree
   +note
 }
 class ActiviteSportive {
   +typeActivite
   +date
   +duree
   +distance
 }
 class SuiviMedicament {
   +nomMedicament
   +instructions
   +dateDebut
   +dateFin
   +heuresRappel
   +statut
 }
 class SuiviCycle {
   +dateDebut
   +dateFin
   +symptomes
   +notes
 }
 class HabitudeQuotidienne {
   +nomHabitude
   +objectif
   +date
   +progression
   +statut
 }
 EtablissementSante "1" --> "0..*" ServiceMedical
 EtablissementSante "1" --> "0..*" RessourceMedicale
 EtablissementSante "1" --> "0..*" Avis
 EtablissementSante "1" --> "0..*" Signalement
 GestionnaireSante "1" --> "0..*" SuiviSommeil
 GestionnaireSante "1" --> "0..*" ActiviteSportive
 GestionnaireSante "1" --> "0..*" SuiviMedicament
 GestionnaireSante "1" --> "0..*" SuiviCycle
 GestionnaireSante "1" --> "0..*" HabitudeQuotidienne
```

## 6. Architecture MVC
- Model : accès aux données (dans ce prototype, collections en session PHP uniquement).
- Controller : reçoit les actions, contrôle les droits, valide les données et choisit la vue.
- View : affiche les pages HTML.
- `Service/` : réservé à l'authentification, aux sessions et aux rôles communs de l'équipe ; à raccorder lors de l'intégration.

## 7. À présenter pendant la validation
1. Montrer la consultation Front Office.
2. Se connecter au Back Office et montrer le CRUD établissement.
3. Montrer la gestion des services et ressources.
4. Déposer un avis puis expliquer la modération.
5. Envoyer un signalement et montrer son traitement administratif.
6. Ouvrir le GestionnaireSante et montrer les catégories de suivi.
7. Expliquer clairement que le stockage par session est temporaire et sera remplacé par la couche d'accès à la base commune.
