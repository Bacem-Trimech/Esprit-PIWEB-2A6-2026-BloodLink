<?php
declare(strict_types=1);

final class CreneauCollecte
{
    public function __construct(
        private ?int $id = null,
        private int $collecteId = 0,
        private string $debut = '',
        private string $fin = '',
        private int $capacite = 1,
        private string $statut = 'OUVERT',
        private int $placesPrises = 0
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (int)($row['collecte_id'] ?? 0),
            (string)($row['debut'] ?? ''),
            (string)($row['fin'] ?? ''),
            (int)($row['capacite'] ?? 1),
            (string)($row['statut'] ?? 'OUVERT'),
            (int)($row['places_prises'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'collecte_id' => $this->collecteId,
            'debut' => $this->debut,
            'fin' => $this->fin,
            'capacite' => $this->capacite,
            'statut' => $this->statut,
            'places_prises' => $this->placesPrises,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCollecteId(): int { return $this->collecteId; }
    public function setCollecteId(int $collecteId): void { $this->collecteId = $collecteId; }

    public function getDebut(): string { return $this->debut; }
    public function setDebut(string $debut): void { $this->debut = $debut; }

    public function getFin(): string { return $this->fin; }
    public function setFin(string $fin): void { $this->fin = $fin; }

    public function getCapacite(): int { return $this->capacite; }
    public function setCapacite(int $capacite): void { $this->capacite = $capacite; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getPlacesPrises(): int { return $this->placesPrises; }
    public function getPlacesLibres(): int { return max(0, $this->capacite - $this->placesPrises); }
}
