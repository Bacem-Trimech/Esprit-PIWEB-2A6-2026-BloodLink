<?php
declare(strict_types=1);

final class AnalyseMedicale
{
    public const TYPES_ECHANTILLON = ['Sang', 'Urine', 'Selles', 'Salive', 'Autre'];

    public function __construct(
        private ?int $id = null,
        private string $nom = '',
        private string $description = '',
        private string $typeEchantillon = 'Sang',
        private float $prix = 0.0,
        private int $delaiHeures = 24,
        private bool $aJeun = false,
        private ?int $etablissementId = null,
        private string $dateAjout = ''
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (string)($row['nom'] ?? ''),
            (string)($row['description'] ?? ''),
            (string)($row['type_echantillon'] ?? 'Sang'),
            (float)($row['prix'] ?? 0),
            (int)($row['delai_heures'] ?? 24),
            (bool)($row['a_jeun'] ?? false),
            isset($row['etablissement_id']) && $row['etablissement_id'] !== '' ? (int)$row['etablissement_id'] : null,
            (string)($row['date_ajout'] ?? date('Y-m-d'))
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'type_echantillon' => $this->typeEchantillon,
            'prix' => $this->prix,
            'delai_heures' => $this->delaiHeures,
            'a_jeun' => $this->aJeun ? 1 : 0,
            'etablissement_id' => $this->etablissementId,
            'date_ajout' => $this->dateAjout,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getTypeEchantillon(): string { return $this->typeEchantillon; }
    public function setTypeEchantillon(string $typeEchantillon): void { $this->typeEchantillon = $typeEchantillon; }

    public function getPrix(): float { return $this->prix; }
    public function setPrix(float $prix): void { $this->prix = $prix; }

    public function getDelaiHeures(): int { return $this->delaiHeures; }
    public function setDelaiHeures(int $delaiHeures): void { $this->delaiHeures = $delaiHeures; }

    public function isAJeun(): bool { return $this->aJeun; }
    public function setAJeun(bool $aJeun): void { $this->aJeun = $aJeun; }

    public function getEtablissementId(): ?int { return $this->etablissementId; }
    public function setEtablissementId(?int $etablissementId): void { $this->etablissementId = $etablissementId; }

    public function getDateAjout(): string { return $this->dateAjout; }
    public function setDateAjout(string $dateAjout): void { $this->dateAjout = $dateAjout; }
}
