<?php
/** Entity: A medicine of the catalogue. Attributes, constructor, getters and setters only (no SQL, no logic). */
class Medicament
{
    public function __construct(
        private int $id = 0,
        private string $name = '',
        private ?string $dci = null,
        private string $form = '',
        private ?string $dosage = null,
        private bool $requiresPrescription = false,
        private string $createdAt = ''
    ) {}

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDci(): ?string { return $this->dci; }
    public function getForm(): string { return $this->form; }
    public function getDosage(): ?string { return $this->dosage; }
    public function getRequiresPrescription(): bool { return $this->requiresPrescription; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setDci(?string $dci): void { $this->dci = $dci; }
    public function setForm(string $form): void { $this->form = $form; }
    public function setDosage(?string $dosage): void { $this->dosage = $dosage; }
    public function setRequiresPrescription(bool $requiresPrescription): void { $this->requiresPrescription = $requiresPrescription; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }

    public static function fromArray(array $r): self
    {
        return new self(
            (int)($r['id'] ?? 0),
            (string)($r['name'] ?? ''),
            $r['dci'] ?? null,
            (string)($r['form'] ?? ''),
            $r['dosage'] ?? null,
            (bool)($r['requires_prescription'] ?? false),
            (string)($r['created_at'] ?? '')
        );
    }
}
