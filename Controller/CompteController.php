<?php
declare(strict_types=1);

final class CompteController
{
    public function profil(): array
    {
        $user = exigerConnexion();
        $q = connexion()->prepare('SELECT id,nom,email,role,actif,telephone,ville,date_naissance,groupe_sanguin,profil_complet,avatar_couleur,qr_identifiant,preferences_contact FROM utilisateurs WHERE id=?');
        $q->execute([$user['id']]);
        $row = $q->fetch();
        return Utilisateur::fromArray($row ?: $user)->toArray();
    }

    public function sauverProfil(array $post): array
    {
        $user = exigerConnexion();
        verifierCsrf();
        $nom = trim((string)($post['nom'] ?? ''));
        $couleur = (string)($post['avatar_couleur'] ?? '#D92735');
        $errors = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) $errors[] = 'Le nom doit contenir 2 à 100 caractères.';
        if (!in_array($couleur, ['#D92735','#3268DC','#15966A','#8B5CF6','#D97706'], true)) $couleur = '#D92735';

        // Pour un compte ADMIN ou RESPONSABLE, seuls le nom et la couleur d'avatar sont requis (pas de groupe sanguin ni date de naissance donneur)
        if ($user['role'] !== 'DONNEUR') {
            if ($errors) return $errors;
            $q = connexion()->prepare('UPDATE utilisateurs SET nom=?,avatar_couleur=?,profil_complet=1 WHERE id=?');
            $q->execute([$nom, $couleur, $user['id']]);
            return [];
        }

        $telephone = trim((string)($post['telephone'] ?? ''));
        $ville = trim((string)($post['ville'] ?? ''));
        $date = trim((string)($post['date_naissance'] ?? ''));
        $groupe = (string)($post['groupe_sanguin'] ?? '');
        if (!preg_match('/^[+0-9 ()-]{8,30}$/D', $telephone)) $errors[] = 'Téléphone invalide.';
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) $errors[] = 'Ville invalide.';
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $minDate = (new DateTimeImmutable('today'))->modify('-100 years');
        $maxDate = (new DateTimeImmutable('today'))->modify('-18 years');
        if (!$d || $d->format('Y-m-d') !== $date || $d > $maxDate || $d < $minDate) {
            $errors[] = 'Date de naissance invalide (âge minimum requis : 18 ans).';
        }
        if ($groupe !== '' && !in_array($groupe, ['O-','O+','A-','A+','B-','B+','AB-','AB+'], true)) $errors[] = 'Groupe sanguin invalide.';
        if ($errors) return $errors;

        $profil = new Utilisateur(
            (int)$user['id'],
            $nom,
            (string)$user['email'],
            '',
            (string)$user['role'],
            true,
            $telephone,
            $ville,
            $date,
            $groupe !== '' ? $groupe : null,
            true,
            $couleur,
            null,
            isset($post['preferences_contact'])
        );

        $q = connexion()->prepare('UPDATE utilisateurs SET nom=?,telephone=?,ville=?,date_naissance=?,groupe_sanguin=?,profil_complet=1,avatar_couleur=?,preferences_contact=? WHERE id=?');
        $q->execute([
            $profil->getNom(),
            $profil->getTelephone(),
            $profil->getVille(),
            $profil->getDateNaissance(),
            $profil->getGroupeSanguin(),
            $profil->getAvatarCouleur(),
            $profil->getPreferencesContact() ? 1 : 0,
            $profil->getId(),
        ]);
        return [];
    }

    public function changerMotDePasse(array $post): array
    {
        $user = exigerConnexion();
        verifierCsrf();
        $q = connexion()->prepare('SELECT mot_de_passe FROM utilisateurs WHERE id=?');
        $q->execute([$user['id']]);
        if (!password_verify((string)($post['ancien'] ?? ''), (string)$q->fetchColumn())) return ['Mot de passe actuel incorrect.'];
        $new = (string)($post['nouveau'] ?? '');
        if (strlen($new) < 12 || $new !== (string)($post['confirmation'] ?? '')) return ['Le nouveau mot de passe doit contenir 12 caractères et être confirmé.'];
        $q = connexion()->prepare('UPDATE utilisateurs SET mot_de_passe=? WHERE id=?');
        $q->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        session_regenerate_id(true);
        return [];
    }

    public function assurerQr(): string
    {
        $user = exigerConnexion();
        $q = connexion()->prepare('SELECT qr_identifiant FROM utilisateurs WHERE id=?');
        $q->execute([$user['id']]);
        $token = $q->fetchColumn();
        if ($token) return (string)$token;
        $token = bin2hex(random_bytes(24));
        $q = connexion()->prepare('UPDATE utilisateurs SET qr_identifiant=? WHERE id=? AND qr_identifiant IS NULL');
        $q->execute([$token, $user['id']]);
        if (!$q->rowCount()) return $this->assurerQr();
        return $token;
    }

    public function qr(): string
    {
        $user = exigerConnexion();
        verifierCsrf();
        $token = bin2hex(random_bytes(24));
        $q = connexion()->prepare('UPDATE utilisateurs SET qr_identifiant=? WHERE id=?');
        $q->execute([$token, $user['id']]);
        return $token;
    }

    public function identifierParQr(string $token): ?array
    {
        exigerRole(['RESPONSABLE','ADMIN']);
        $token = strtolower(trim($token));
        if (!preg_match('/^[a-f0-9]{48}$/D', $token)) return null;
        $q = connexion()->prepare("SELECT id,nom,email,role,actif,telephone,ville,date_naissance,groupe_sanguin FROM utilisateurs WHERE qr_identifiant=? AND actif=1 AND role='DONNEUR'");
        $q->execute([$token]);
        $row = $q->fetch();
        return $row ? Utilisateur::fromArray($row)->toArray() : null;
    }
}
