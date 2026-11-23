<?php
declare(strict_types=1);

final class ServiceMedical
{
    public function __construct(
        private ?int $id = null,
        private int $etablissementId = 0,
        private string $nom = '',
        private string $specialite = '',
        private ?string $horaires = null,
        private ?string $telephone = null,
        private bool $estUrgence = false,
        private ?string $description = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (int)($row['etablissement_id'] ?? 0),
            (string)($row['nom'] ?? ''),
            (string)($row['specialite'] ?? ''),
            isset($row['horaires']) && $row['horaires'] !== '' ? (string)$row['horaires'] : null,
            isset($row['telephone']) && $row['telephone'] !== '' ? (string)$row['telephone'] : null,
            (bool)($row['est_urgence'] ?? false),
            isset($row['description']) && $row['description'] !== '' ? (string)$row['description'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'etablissement_id' => $this->etablissementId,
            'nom' => $this->nom,
            'specialite' => $this->specialite,
            'horaires' => $this->horaires,
            'telephone' => $this->telephone,
            'est_urgence' => $this->estUrgence ? 1 : 0,
            'description' => $this->description,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getEtablissementId(): int { return $this->etablissementId; }
    public function setEtablissementId(int $etablissementId): void { $this->etablissementId = $etablissementId; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }

    public function getSpecialite(): string { return $this->specialite; }
    public function setSpecialite(string $specialite): void { $this->specialite = $specialite; }

    public function getHoraires(): ?string { return $this->horaires; }
    public function setHoraires(?string $horaires): void { $this->horaires = $horaires; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }

    public function isEstUrgence(): bool { return $this->estUrgence; }
    public function setEstUrgence(bool $estUrgence): void { $this->estUrgence = $estUrgence; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
}
