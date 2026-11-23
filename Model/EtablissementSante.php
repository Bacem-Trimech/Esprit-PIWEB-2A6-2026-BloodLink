<?php
declare(strict_types=1);

final class EtablissementSante
{
    public const TYPES = [
        'hopital' => 'Hôpital',
        'clinique' => 'Clinique',
        'polyclinique' => 'Polyclinique',
        'pharmacie' => 'Pharmacie',
        'laboratoire' => 'Laboratoire',
    ];

    public function __construct(
        private ?int $id = null,
        private string $nom = '',
        private string $type = 'hopital',
        private string $ville = '',
        private string $adresse = '',
        private ?string $telephone = null,
        private ?string $email = null,
        private ?string $description = null,
        private ?string $creeLe = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (string)($row['nom'] ?? $row['name'] ?? ''),
            (string)($row['type'] ?? 'hopital'),
            (string)($row['ville'] ?? $row['city'] ?? ''),
            (string)($row['adresse'] ?? $row['address'] ?? ''),
            isset($row['telephone']) && $row['telephone'] !== '' ? (string)$row['telephone'] : (isset($row['phone']) ? (string)$row['phone'] : null),
            isset($row['email']) && $row['email'] !== '' ? (string)$row['email'] : null,
            isset($row['description']) && $row['description'] !== '' ? (string)$row['description'] : null,
            isset($row['cree_le']) ? (string)$row['cree_le'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => $this->type,
            'ville' => $this->ville,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'description' => $this->description,
            'cree_le' => $this->creeLe,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function getName(): string { return $this->nom; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = $type; }

    public function getVille(): string { return $this->ville; }
    public function setVille(string $ville): void { $this->ville = $ville; }
    public function getCity(): string { return $this->ville; }

    public function getAdresse(): string { return $this->adresse; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function getAddress(): string { return $this->adresse; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }
    public function getPhone(): ?string { return $this->telephone; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getCreeLe(): ?string { return $this->creeLe; }
    public function setCreeLe(?string $creeLe): void { $this->creeLe = $creeLe; }
}
