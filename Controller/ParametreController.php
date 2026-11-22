<?php
declare(strict_types=1);

final class ParametreController
{
    public function tous(): array
    {
        $user=exigerRole(['RESPONSABLE']);
        $q=connexion()->prepare('SELECT nom_organisation,contact_public,capacite_defaut,horizon_reservation_jours,message_accueil FROM parametres_responsable WHERE responsable_id=?');
        $q->execute([$user['id']]);
        return $q->fetch() ?: [
            'nom_organisation'=>'BloodLink', 'contact_public'=>'',
            'capacite_defaut'=>2, 'horizon_reservation_jours'=>60,
            'message_accueil'=>'Bienvenue à notre collecte de sang.',
        ];
    }
    public function sauver(array $post): array
    {
        $user=exigerRole(['RESPONSABLE']);
        verifierCsrf();
        $nom=trim((string)($post['nom_organisation']??''));
        $contact=trim((string)($post['contact_public']??''));
        $capacite=(string)($post['capacite_defaut']??'');
        $horizon=(string)($post['horizon_reservation_jours']??'');
        $message=trim((string)($post['message_accueil']??''));
        $errors=[];
        if (mb_strlen($nom)<2 || mb_strlen($nom)>100) $errors[]='Nom de l’organisation invalide.';
        if ($contact!=='' && (strlen($contact)>190 || !filter_var($contact,FILTER_VALIDATE_EMAIL))) $errors[]='E-mail public invalide.';
        if (!ctype_digit($capacite) || (int)$capacite<1 || (int)$capacite>100) $errors[]='Capacité par défaut invalide.';
        if (!ctype_digit($horizon) || (int)$horizon<1 || (int)$horizon>365) $errors[]='Horizon de réservation invalide.';
        if (mb_strlen($message)<5 || mb_strlen($message)>300) $errors[]='Message d’accueil invalide.';
        if ($errors) return $errors;
        $q=connexion()->prepare('INSERT INTO parametres_responsable (responsable_id,nom_organisation,contact_public,capacite_defaut,horizon_reservation_jours,message_accueil) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nom_organisation=VALUES(nom_organisation),contact_public=VALUES(contact_public),capacite_defaut=VALUES(capacite_defaut),horizon_reservation_jours=VALUES(horizon_reservation_jours),message_accueil=VALUES(message_accueil)');
        $q->execute([$user['id'],$nom,$contact,(int)$capacite,(int)$horizon,$message]);
        return [];
    }
}
