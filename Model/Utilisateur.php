<?php
declare(strict_types=1);

final class Utilisateur
{
    public function __construct(
        private ?int $id = null,
        private string $nom = '',
        private string $email = '',
        private string $motDePasse = '',
        private string $role = 'DONNEUR',
        private bool $actif = true,
        private ?string $telephone = null,
        private ?string $ville = null,
        private ?string $dateNaissance = null,
        private ?string $groupeSanguin = null,
        private bool $profilComplet = false,
        private ?string $avatarCouleur = null,
        private ?string $qrIdentifiant = null,
        private bool $preferencesContact = false,
        private ?string $invitationHash = null,
        private ?string $invitationExpire = null,
        private ?string $creeLe = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (string)($row['nom'] ?? ''),
            (string)($row['email'] ?? ''),
            (string)($row['mot_de_passe'] ?? ''),
            (string)($row['role'] ?? 'DONNEUR'),
            (bool)($row['actif'] ?? true),
            isset($row['telephone']) && $row['telephone'] !== '' ? (string)$row['telephone'] : null,
            isset($row['ville']) && $row['ville'] !== '' ? (string)$row['ville'] : null,
            isset($row['date_naissance']) && $row['date_naissance'] !== '' ? (string)$row['date_naissance'] : null,
            isset($row['groupe_sanguin']) && $row['groupe_sanguin'] !== '' ? (string)$row['groupe_sanguin'] : null,
            (bool)($row['profil_complet'] ?? false),
            isset($row['avatar_couleur']) && $row['avatar_couleur'] !== '' ? (string)$row['avatar_couleur'] : null,
            isset($row['qr_identifiant']) && $row['qr_identifiant'] !== '' ? (string)$row['qr_identifiant'] : null,
            (bool)($row['preferences_contact'] ?? false),
            isset($row['invitation_hash']) ? (string)$row['invitation_hash'] : null,
            isset($row['invitation_expire']) ? (string)$row['invitation_expire'] : null,
            isset($row['cree_le']) ? (string)$row['cree_le'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'role' => $this->role,
            'actif' => $this->actif ? 1 : 0,
            'telephone' => $this->telephone,
            'ville' => $this->ville,
            'date_naissance' => $this->dateNaissance,
            'groupe_sanguin' => $this->groupeSanguin,
            'profil_complet' => $this->profilComplet ? 1 : 0,
            'avatar_couleur' => $this->avatarCouleur,
            'qr_identifiant' => $this->qrIdentifiant,
            'preferences_contact' => $this->preferencesContact ? 1 : 0,
            'cree_le' => $this->creeLe,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getMotDePasse(): string { return $this->motDePasse; }
    public function setMotDePasse(string $motDePasse): void { $this->motDePasse = $motDePasse; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): void { $this->role = $role; }

    public function estActif(): bool { return $this->actif; }
    public function setActif(bool $actif): void { $this->actif = $actif; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): void { $this->ville = $ville; }

    public function getDateNaissance(): ?string { return $this->dateNaissance; }
    public function setDateNaissance(?string $dateNaissance): void { $this->dateNaissance = $dateNaissance; }

    public function getGroupeSanguin(): ?string { return $this->groupeSanguin; }
    public function setGroupeSanguin(?string $groupeSanguin): void { $this->groupeSanguin = $groupeSanguin; }

    public function estProfilComplet(): bool { return $this->profilComplet; }
    public function setProfilComplet(bool $profilComplet): void { $this->profilComplet = $profilComplet; }

    public function getAvatarCouleur(): ?string { return $this->avatarCouleur; }
    public function setAvatarCouleur(?string $avatarCouleur): void { $this->avatarCouleur = $avatarCouleur; }

    public function getQrIdentifiant(): ?string { return $this->qrIdentifiant; }
    public function setQrIdentifiant(?string $qrIdentifiant): void { $this->qrIdentifiant = $qrIdentifiant; }

    public function getPreferencesContact(): bool { return $this->preferencesContact; }
    public function setPreferencesContact(bool $preferencesContact): void { $this->preferencesContact = $preferencesContact; }

    public function getInvitationHash(): ?string { return $this->invitationHash; }
    public function setInvitationHash(?string $invitationHash): void { $this->invitationHash = $invitationHash; }

    public function getInvitationExpire(): ?string { return $this->invitationExpire; }
    public function setInvitationExpire(?string $invitationExpire): void { $this->invitationExpire = $invitationExpire; }

    public function getCreeLe(): ?string { return $this->creeLe; }
}
