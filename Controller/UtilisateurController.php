<?php
declare(strict_types=1);

final class UtilisateurController
{
    public function liste(): array
    {
        exigerRole(['ADMIN']);
        $rows = connexion()->query('SELECT id,nom,email,role,actif,cree_le FROM utilisateurs ORDER BY id DESC')->fetchAll();
        return array_map(static fn(array $r): array => Utilisateur::fromArray($r)->toArray(), $rows);
    }

    public function trouver(int $id): ?array
    {
        exigerRole(['ADMIN']);
        $q = connexion()->prepare('SELECT id,nom,email,role,actif FROM utilisateurs WHERE id=?');
        $q->execute([$id]);
        $row = $q->fetch();
        return $row ? Utilisateur::fromArray($row)->toArray() : null;
    }

    public function enregistrer(array $post, ?int $id = null): array
    {
        $admin = exigerRole(['ADMIN']);
        verifierCsrf();
        $role = (string)($post['role'] ?? 'DONNEUR');

        // Pour un compte existant, l'administrateur modifie uniquement le rôle (jamais le nom, l'e-mail ou le mot de passe)
        if ($id !== null && $id > 0) {
            if (!in_array($role, ['DONNEUR','RESPONSABLE','ADMIN'], true)) {
                return ['Rôle invalide.'];
            }
            if ($id === (int)$admin['id'] && $role !== 'ADMIN') {
                return ['Vous ne pouvez pas retirer votre propre rôle administrateur.'];
            }
            $q = connexion()->prepare('UPDATE utilisateurs SET role=? WHERE id=?');
            $q->execute([$role, $id]);
            return [];
        }

        // Création d'un nouveau compte donneur
        $nom = trim((string)($post['nom'] ?? ''));
        $email = mb_strtolower(trim((string)($post['email'] ?? '')));
        $actif = isset($post['actif']);
        $pass = (string)($post['mot_de_passe'] ?? '');
        $errors = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) $errors[] = 'Nom invalide.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'E-mail invalide.';
        if ($role !== 'DONNEUR') $errors[] = 'Invitez les responsables depuis la page prévue. Les comptes administrateurs sont attribués à des comptes existants.';
        if (strlen($pass) < 12) $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
        if ($errors) return $errors;

        $utilisateur = new Utilisateur(
            null,
            $nom,
            $email,
            password_hash($pass, PASSWORD_DEFAULT),
            'DONNEUR',
            $actif
        );

        try {
            $q = connexion()->prepare('INSERT INTO utilisateurs (nom,email,mot_de_passe,role,actif) VALUES (?,?,?,?,?)');
            $q->execute([
                $utilisateur->getNom(),
                $utilisateur->getEmail(),
                $utilisateur->getMotDePasse(),
                $utilisateur->getRole(),
                $utilisateur->estActif() ? 1 : 0,
            ]);
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
        if ($id === (int)$admin['id']) throw new RuntimeException('Vous ne pouvez pas désactiver votre propre compte.');
        $q = connexion()->prepare('UPDATE utilisateurs SET actif=IF(actif=1,0,1) WHERE id=?');
        $q->execute([$id]);
    }
}
