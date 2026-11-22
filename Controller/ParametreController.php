<?php
declare(strict_types=1);

final class ParametreController
{
    public function tous(): array
    {
        exigerRole(['ADMIN']);
        return connexion()->query('SELECT cle,valeur FROM parametres ORDER BY cle')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    public function sauver(array $post): array
    {
        $user=exigerRole(['ADMIN']);
        verifierCsrf();
        $allowed=[
            'nom_organisation'=>[2,100],
            'contact_public'=>[0,190],
            'capacite_defaut'=>[1,3],
            'horizon_reservation_jours'=>[1,3],
            'message_accueil'=>[5,300],
        ];
        $errors=[];
        foreach($allowed as $key=>[$min,$max]) {
            $value=trim((string)($post[$key]??''));
            if (mb_strlen($value)<$min || mb_strlen($value)>$max) $errors[]='Valeur invalide : '.$key;
            if ($key==='contact_public' && $value!=='' && !filter_var($value,FILTER_VALIDATE_EMAIL)) $errors[]='E-mail de contact invalide.';
            if ($key==='capacite_defaut' && (!ctype_digit($value) || (int)$value<1 || (int)$value>100)) $errors[]='Capacité par défaut invalide.';
            if ($key==='horizon_reservation_jours' && (!ctype_digit($value) || (int)$value<1 || (int)$value>365)) $errors[]='Horizon de réservation invalide.';
        }
        if ($errors) return $errors;
        $q=connexion()->prepare('INSERT INTO parametres (cle,valeur,modifie_par) VALUES (?,?,?) ON DUPLICATE KEY UPDATE valeur=VALUES(valeur),modifie_par=VALUES(modifie_par)');
        foreach(array_keys($allowed) as $key) $q->execute([$key,trim((string)$post[$key]),$user['id']]);
        return [];
    }
}
