<?php
/** Entity: A health establishment (shared with the Annuaire medical module; here only pharmacies are used). Attributes, constructor, getters and setters only (no SQL, no logic). */
class EtablissementSante
{
    public function __construct(
        private int $id = 0,
        private string $name = '',
        private string $type = 'pharmacie',
        private string $city = '',
        private string $address = '',
        private ?string $phone = null
    ) {}

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function getCity(): string { return $this->city; }
    public function getAddress(): string { return $this->address; }
    public function getPhone(): ?string { return $this->phone; }

    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setType(string $type): void { $this->type = $type; }
    public function setCity(string $city): void { $this->city = $city; }
    public function setAddress(string $address): void { $this->address = $address; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }

    public static function fromArray(array $r): self
    {
        return new self(
            (int)($r['id'] ?? 0),
            (string)($r['name'] ?? ''),
            (string)($r['type'] ?? ''),
            (string)($r['city'] ?? ''),
            (string)($r['address'] ?? ''),
            $r['phone'] ?? null
        );
    }
}
