<?php
declare(strict_types=1);

final class InvitationController
{
    public function inviter(array $post): string
    {
        exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        if (!isset($post['consentement'])) throw new RuntimeException('Le consentement du donneur est nécessaire.');
        $nom = trim((string)($post['nom'] ?? ''));
        $email = mb_strtolower(trim((string)($post['email'] ?? '')));
        if (mb_strlen($nom)<2 || mb_strlen($nom)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Nom ou adresse e-mail invalide.');
        $token = bin2hex(random_bytes(32));
        $q = connexion()->prepare("INSERT INTO utilisateurs (nom,email,mot_de_passe,role,actif,invitation_hash,invitation_expire) VALUES (?,?,?,'DONNEUR',0,?,DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        try {
            $q->execute([$nom,$email,password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),hash('sha256',$token)]);
        } catch (PDOException $error) {
            if ($error->getCode()==='23000') throw new RuntimeException('Un compte existe déjà avec cette adresse. Recherchez son identifiant.');
            throw $error;
        }
        $_SESSION['invitation_lien'] = 'index.php?page=activer&jeton='.$token;
        $_SESSION['invitation_id'] = (int)connexion()->lastInsertId();
        return $token;
    }
    public function activer(array $post): void
    {
        verifierCsrf();
        $token = (string)($post['jeton'] ?? '');
        $pass = (string)($post['mot_de_passe'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/D',$token) || strlen($pass)<12 || $pass !== (string)($post['confirmation'] ?? '')) throw new RuntimeException('Lien ou mot de passe invalide.');
        $q = connexion()->prepare("UPDATE utilisateurs SET mot_de_passe=?,actif=1,invitation_hash=NULL,invitation_expire=NULL WHERE invitation_hash=? AND invitation_expire>NOW() AND actif=0");
        $q->execute([password_hash($pass,PASSWORD_DEFAULT),hash('sha256',$token)]);
        if (!$q->rowCount()) throw new RuntimeException('Ce lien a expiré ou a déjà été utilisé.');
    }
}
