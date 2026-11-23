<?php
declare(strict_types=1);

final class SosController
{
    public const GROUPES = ['O-','O+','A-','A+','B-','B+','AB-','AB+'];

    public static function compatibles(string $receveur): array
    {
        return match($receveur) {
            'O-' => ['O-'],
            'O+' => ['O-','O+'],
            'A-' => ['O-','A-'],
            'A+' => ['O-','O+','A-','A+'],
            'B-' => ['O-','B-'],
            'B+' => ['O-','O+','B-','B+'],
            'AB-' => ['O-','A-','B-','AB-'],
            'AB+' => self::GROUPES,
            default => [],
        };
    }

    public function liste(bool $admin = false): array
    {
        $sql = 'SELECT id,lieu,ville,groupe_receveur,telephone,message,cree_le,expire_le FROM alertes_sos WHERE supprime_le IS NULL AND expire_le>NOW() ORDER BY cree_le DESC';
        $rows = connexion()->query($sql)->fetchAll();
        return array_map(static fn(array $row): array => AlerteSos::fromArray($row)->toArray(), $rows);
    }

    public function creer(array $post): array
    {
        verifierCsrf();
        $lieu = trim((string)($post['lieu'] ?? ''));
        $ville = trim((string)($post['ville'] ?? ''));
        $groupe = (string)($post['groupe_receveur'] ?? '');
        $tel = trim((string)($post['telephone'] ?? ''));
        $message = trim((string)($post['message'] ?? ''));
        $errors = [];
        if (mb_strlen($lieu) < 5 || mb_strlen($lieu) > 200) $errors[] = 'Lieu invalide (5 à 200 caractères).';
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) $errors[] = 'Ville invalide (2 à 100 caractères).';
        if (!in_array($groupe, self::GROUPES, true)) $errors[] = 'Groupe sanguin invalide.';
        if (!preg_match('/^[+0-9 ()-]{8,30}$/D', $tel)) $errors[] = 'Téléphone invalide.';
        if (mb_strlen($message) > 500) $errors[] = 'Message trop long (500 caractères maximum).';
        if (!isset($post['consentement'])) $errors[] = 'Acceptez la publication du numéro de contact pendant deux heures.';
        if ($errors) return $errors;

        $ipHash = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'local'));
        $q = connexion()->prepare('SELECT COUNT(*) FROM alertes_sos WHERE ip_hash=? AND cree_le>DATE_SUB(NOW(),INTERVAL 10 MINUTE)');
        $q->execute([$ipHash]);
        if ($q->fetchColumn()) return ['Veuillez patienter avant de publier une autre alerte.'];

        $alerte = new AlerteSos(
            null,
            $lieu,
            $ville,
            $groupe,
            $tel,
            $message !== '' ? $message : null,
            $ipHash
        );

        $q = connexion()->prepare('INSERT INTO alertes_sos (lieu,ville,groupe_receveur,telephone,message,ip_hash,expire_le) VALUES (?,?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 2 HOUR))');
        $q->execute([
            $alerte->getLieu(),
            $alerte->getVille(),
            $alerte->getGroupeReceveur(),
            $alerte->getTelephone(),
            $alerte->getMessage(),
            $alerte->getIpHash(),
        ]);
        return [];
    }

    public function supprimer(int $id): void
    {
        exigerRole(['ADMIN']);
        verifierCsrf();
        $q = connexion()->prepare('UPDATE alertes_sos SET supprime_le=NOW() WHERE id=? AND supprime_le IS NULL');
        $q->execute([$id]);
    }
}
