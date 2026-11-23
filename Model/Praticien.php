<?php
declare(strict_types=1);

final class Praticien
{
    public function __construct(
        private ?int $id = null,
        private string $nom = '',
        private string $prenom = '',
        private string $specialite = '',
        private ?string $telephone = null,
        private ?string $email = null,
        private ?string $adresse = null,
        private ?int $etablissementId = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id_praticien']) ? (int)$row['id_praticien'] : (isset($row['id']) ? (int)$row['id'] : null),
            (string)($row['nom'] ?? ''),
            (string)($row['prenom'] ?? ''),
            (string)($row['specialite'] ?? ''),
            isset($row['telephone']) && $row['telephone'] !== '' ? (string)$row['telephone'] : null,
            isset($row['email']) && $row['email'] !== '' ? (string)$row['email'] : null,
            isset($row['adresse']) && $row['adresse'] !== '' ? (string)$row['adresse'] : null,
            isset($row['etablissement_id']) && $row['etablissement_id'] !== '' ? (int)$row['etablissement_id'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id_praticien' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'specialite' => $this->specialite,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'adresse' => $this->adresse,
            'etablissement_id' => $this->etablissementId,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }

    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }

    public function getSpecialite(): string { return $this->specialite; }
    public function setSpecialite(string $specialite): void { $this->specialite = $specialite; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): void { $this->adresse = $adresse; }

    public function getEtablissementId(): ?int { return $this->etablissementId; }
    public function setEtablissementId(?int $etablissementId): void { $this->etablissementId = $etablissementId; }
}
