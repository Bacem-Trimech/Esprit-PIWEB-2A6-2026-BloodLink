<?php
declare(strict_types=1);

final class ParametreController
{
    public function tous(): array
    {
        $user = exigerRole(['RESPONSABLE']);
        $q = connexion()->prepare('SELECT responsable_id,nom_organisation,contact_public,capacite_defaut,horizon_reservation_jours,message_accueil FROM parametres_responsable WHERE responsable_id=?');
        $q->execute([$user['id']]);
        $row = $q->fetch();
        return ParametreResponsable::fromArray($row ?: [], (int)$user['id'])->toArray();
    }

    public function sauver(array $post): array
    {
        $user = exigerRole(['RESPONSABLE']);
        verifierCsrf();
        $actuel = $this->tous();
        $nom = trim((string)($post['nom_organisation'] ?? ''));
        $capacite = trim((string)($post['capacite_defaut'] ?? ''));
        $contact = isset($post['contact_public']) ? trim((string)$post['contact_public']) : (string)($actuel['contact_public'] ?? '');
        $horizon = isset($post['horizon_reservation_jours']) && trim((string)$post['horizon_reservation_jours']) !== ''
            ? trim((string)$post['horizon_reservation_jours'])
            : (string)($actuel['horizon_reservation_jours'] ?? 60);
        $message = isset($post['message_accueil']) && trim((string)$post['message_accueil']) !== ''
            ? trim((string)$post['message_accueil'])
            : (string)($actuel['message_accueil'] ?? 'Bienvenue à notre collecte de sang.');

        $errors = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) $errors[] = 'Nom du centre invalide (2 à 100 caractères).';
        if (!ctype_digit($capacite) || (int)$capacite < 1 || (int)$capacite > 100) $errors[] = 'Places par créneau invalides (1 à 100).';
        if ($contact !== '' && (strlen($contact) > 190 || !filter_var($contact, FILTER_VALIDATE_EMAIL))) $errors[] = 'E-mail public invalide.';
        if (!ctype_digit($horizon) || (int)$horizon < 1 || (int)$horizon > 365) $horizon = '60';
        if (mb_strlen($message) < 5 || mb_strlen($message) > 300) $message = 'Bienvenue à notre collecte de sang.';
        if ($errors) return $errors;

        $parametre = new ParametreResponsable(
            (int)$user['id'],
            $nom,
            $contact,
            (int)$capacite,
            (int)$horizon,
            $message
        );

        $q = connexion()->prepare('INSERT INTO parametres_responsable (responsable_id,nom_organisation,contact_public,capacite_defaut,horizon_reservation_jours,message_accueil) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nom_organisation=VALUES(nom_organisation),contact_public=VALUES(contact_public),capacite_defaut=VALUES(capacite_defaut),horizon_reservation_jours=VALUES(horizon_reservation_jours),message_accueil=VALUES(message_accueil)');
        $q->execute([
            $parametre->getResponsableId(),
            $parametre->getNomOrganisation(),
            $parametre->getContactPublic(),
            $parametre->getCapaciteDefaut(),
            $parametre->getHorizonReservationJours(),
            $parametre->getMessageAccueil(),
        ]);
        return [];
    }
}
