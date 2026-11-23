<?php
/** Entity: Stock of a medicine declared by a pharmacy. The two names come from JOINs. Attributes, constructor, getters and setters only (no SQL, no logic). */
class Disponibilite
{
    public function __construct(
        private int $id = 0,
        private int $etablissementId = 0,
        private int $medicamentId = 0,
        private int $quantity = 0,
        private string $updatedAt = '',
        private string $etablissementName = '',
        private string $medicamentName = ''
    ) {}

    public function getId(): int { return $this->id; }
    public function getEtablissementId(): int { return $this->etablissementId; }
    public function getMedicamentId(): int { return $this->medicamentId; }
    public function getQuantity(): int { return $this->quantity; }
    public function getUpdatedAt(): string { return $this->updatedAt; }
    public function getEtablissementName(): string { return $this->etablissementName; }
    public function getMedicamentName(): string { return $this->medicamentName; }

    public function setId(int $id): void { $this->id = $id; }
    public function setEtablissementId(int $etablissementId): void { $this->etablissementId = $etablissementId; }
    public function setMedicamentId(int $medicamentId): void { $this->medicamentId = $medicamentId; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }
    public function setUpdatedAt(string $updatedAt): void { $this->updatedAt = $updatedAt; }
    public function setEtablissementName(string $etablissementName): void { $this->etablissementName = $etablissementName; }
    public function setMedicamentName(string $medicamentName): void { $this->medicamentName = $medicamentName; }

    public static function fromArray(array $r): self
    {
        return new self(
            (int)($r['id'] ?? 0),
            (int)($r['etablissement_id'] ?? 0),
            (int)($r['medicament_id'] ?? 0),
            (int)($r['quantity'] ?? 0),
            (string)($r['updated_at'] ?? ''),
            (string)($r['etablissement_name'] ?? ''),
            (string)($r['medicament_name'] ?? '')
        );
    }
}
