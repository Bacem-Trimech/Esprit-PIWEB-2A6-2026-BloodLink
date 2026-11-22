<?php
declare(strict_types=1);

final class UtilisateurController
{
    public function liste(): array
    {
        exigerRole(['ADMIN']);
        return connexion()->query('SELECT id,nom,email,role,actif,cree_le FROM utilisateurs ORDER BY id DESC')->fetchAll();
    }
    public function trouver(int $id): ?array
    {
        exigerRole(['ADMIN']);
        $q = connexion()->prepare('SELECT id,nom,email,role,actif FROM utilisateurs WHERE id=?');
        $q->execute([$id]);
        return $q->fetch() ?: null;
    }
    public function enregistrer(array $post, ?int $id = null): array
    {
        $admin = exigerRole(['ADMIN']);
        verifierCsrf();
        $nom = trim((string)($post['nom'] ?? ''));
        $email = mb_strtolower(trim((string)($post['email'] ?? '')));
        $role = (string)($post['role'] ?? '');
        $actif = isset($post['actif']) ? 1 : 0;
        $pass = (string)($post['mot_de_passe'] ?? '');
        $errors = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) $errors[] = 'Nom invalide.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'E-mail invalide.';
        if (!in_array($role, ['DONNEUR','RESPONSABLE','ADMIN'], true)) $errors[] = 'Rôle invalide.';
        if ($id===null && $role!=='DONNEUR') $errors[]='Invitez les responsables depuis la page prévue. Les comptes administrateurs sont attribués à des comptes existants.';
        if (($id === null || $pass !== '') && strlen($pass) < 12) $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
        if ($id === (int)$admin['id'] && ($role !== 'ADMIN' || !$actif)) $errors[] = 'Vous ne pouvez pas retirer votre propre accès administrateur.';
        if ($errors) return $errors;
        try {
            if ($id === null) {
                $q = connexion()->prepare('INSERT INTO utilisateurs (nom,email,mot_de_passe,role,actif) VALUES (?,?,?,?,?)');
                $q->execute([$nom,$email,password_hash($pass,PASSWORD_DEFAULT),$role,$actif]);
            } else {
                $sql = 'UPDATE utilisateurs SET nom=?,email=?,role=?,actif=?';
                $values = [$nom,$email,$role,$actif];
                if ($pass !== '') {
                    $sql .= ',mot_de_passe=?';
                    $values[] = password_hash($pass,PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE id=?';
                $values[] = $id;
                $q = connexion()->prepare($sql);
                $q->execute($values);
            }
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') return ['Cette adresse e-mail est déjà utilisée.'];
            throw $error;
        }
        return [];
    }
    public function supprimer(int $id): void
    {
        $admin = exigerRole(['ADMIN']);
        verifierCsrf();
        if ($id === (int)$admin['id']) throw new RuntimeException('Vous ne pouvez pas supprimer votre propre compte.');
        $q = connexion()->prepare('UPDATE utilisateurs SET actif=0 WHERE id=?');
        $q->execute([$id]);
    }
}
