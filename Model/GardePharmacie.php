<?php
/** Entity: A duty period (garde) of a pharmacy. etablissementName comes from a JOIN. Attributes, constructor, getters and setters only (no SQL, no logic). */
class GardePharmacie
{
    public function __construct(
        private int $id = 0,
        private int $etablissementId = 0,
        private string $startDate = '',
        private string $endDate = '',
        private ?string $notes = null,
        private string $etablissementName = ''
    ) {}

    public function getId(): int { return $this->id; }
    public function getEtablissementId(): int { return $this->etablissementId; }
    public function getStartDate(): string { return $this->startDate; }
    public function getEndDate(): string { return $this->endDate; }
    public function getNotes(): ?string { return $this->notes; }
    public function getEtablissementName(): string { return $this->etablissementName; }

    public function setId(int $id): void { $this->id = $id; }
    public function setEtablissementId(int $etablissementId): void { $this->etablissementId = $etablissementId; }
    public function setStartDate(string $startDate): void { $this->startDate = $startDate; }
    public function setEndDate(string $endDate): void { $this->endDate = $endDate; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function setEtablissementName(string $etablissementName): void { $this->etablissementName = $etablissementName; }

    public static function fromArray(array $r): self
    {
        return new self(
            (int)($r['id'] ?? 0),
            (int)($r['etablissement_id'] ?? 0),
            (string)($r['start_date'] ?? ''),
            (string)($r['end_date'] ?? ''),
            $r['notes'] ?? null,
            (string)($r['etablissement_name'] ?? '')
        );
    }
}
