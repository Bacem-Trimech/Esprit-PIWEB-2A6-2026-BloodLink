<?php
declare(strict_types=1);

final class InvitationController
{
    public function inviterDonneur(array $post): void
    {
        exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        if (!isset($post['consentement'])) throw new RuntimeException('Le consentement du donneur est nécessaire.');
        $this->creer($post, 'DONNEUR');
    }
    public function inviterResponsable(array $post): void
    {
        exigerRole(['ADMIN']);
        verifierCsrf();
        $this->creer($post, 'RESPONSABLE');
    }
    private function creer(array $post, string $role): void
    {
        $nom=trim((string)($post['nom']??''));
        $email=mb_strtolower(trim((string)($post['email']??'')));
        $telephone=trim((string)($post['telephone']??''));
        $ville=trim((string)($post['ville']??''));
        $date=trim((string)($post['date_naissance']??''));
        if (mb_strlen($nom)<2 || mb_strlen($nom)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) throw new RuntimeException('Nom ou adresse e-mail invalide.');
        $birthday=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if ($role==='DONNEUR' && (!preg_match('/^[+0-9 ()-]{8,30}$/D',$telephone) || mb_strlen($ville)<2 || !$birthday || $birthday->format('Y-m-d')!==$date || $birthday>new DateTimeImmutable('today'))) throw new RuntimeException('Téléphone, ville ou date de naissance invalide.');
        $token=bin2hex(random_bytes(32));
        $hash=hash('sha256',$token);
        $q=connexion()->prepare('SELECT id,actif,role FROM utilisateurs WHERE email=?');
        $q->execute([$email]); $existing=$q->fetch();
        if ($existing) {
            if ($existing['actif']) throw new RuntimeException('Un compte actif existe déjà. Demandez à l’administrateur de modifier son rôle si nécessaire.');
            if ($existing['role']!==$role) throw new RuntimeException('Cette adresse possède une invitation pour un autre rôle.');
            $q=connexion()->prepare('UPDATE utilisateurs SET nom=?,telephone=?,ville=?,date_naissance=?,invitation_hash=?,invitation_expire=DATE_ADD(NOW(),INTERVAL 30 DAY) WHERE id=?');
            $q->execute([$nom,$telephone?:null,$ville?:null,$date?:null,$hash,$existing['id']]);
            $id=(int)$existing['id'];
        } else {
            $q=connexion()->prepare('INSERT INTO utilisateurs (nom,email,mot_de_passe,role,actif,telephone,ville,date_naissance,invitation_hash,invitation_expire) VALUES (?,?,?,?,0,?,?,?,?,DATE_ADD(NOW(),INTERVAL 30 DAY))');
            $q->execute([$nom,$email,password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),$role,$telephone?:null,$ville?:null,$date?:null,$hash]);
            $id=(int)connexion()->lastInsertId();
        }
        $_SESSION['invitation_lien']='index.php?page=activer&jeton='.$token;
        $_SESSION['invitation_id']=$id;
    }
    public function activer(array $post): void
    {
        verifierCsrf();
        $token=(string)($post['jeton']??'');
        $pass=(string)($post['mot_de_passe']??'');
        $telephone=trim((string)($post['telephone']??''));
        $ville=trim((string)($post['ville']??''));
        $date=trim((string)($post['date_naissance']??''));
        if (!preg_match('/^[a-f0-9]{64}$/D',$token) || strlen($pass)<12 || $pass!==(string)($post['confirmation']??'')) throw new RuntimeException('Lien ou mot de passe invalide.');
        $birthday=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if (!preg_match('/^[+0-9 ()-]{8,30}$/D',$telephone) || mb_strlen($ville)<2 || !$birthday || $birthday->format('Y-m-d')!==$date || $birthday>new DateTimeImmutable('today')) throw new RuntimeException('Complétez le téléphone, la ville et la date de naissance.');
        $q=connexion()->prepare('UPDATE utilisateurs SET mot_de_passe=?,telephone=?,ville=?,date_naissance=?,actif=1,profil_complet=1,invitation_hash=NULL,invitation_expire=NULL WHERE invitation_hash=? AND invitation_expire>NOW() AND actif=0');
        $q->execute([password_hash($pass,PASSWORD_DEFAULT),$telephone,$ville,$date,hash('sha256',$token)]);
        if (!$q->rowCount()) throw new RuntimeException('Ce lien a expiré ou a déjà été utilisé.');
    }
}
