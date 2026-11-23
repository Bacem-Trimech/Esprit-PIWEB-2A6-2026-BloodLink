<?php
declare(strict_types=1);

final class ExamenPreDon
{
    public function __construct(
        private int $participationId,
        private ?int $tensionSystolique = null,
        private ?int $tensionDiastolique = null,
        private ?int $pouls = null,
        private ?float $temperatureC = null,
        private ?float $hemoglobineGDl = null,
        private ?float $poidsKg = null,
        private string $decision = 'EN_ATTENTE',
        private int $saisiPar = 0,
        private ?string $saisiLe = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['participation_id'] ?? $row['id'] ?? 0),
            isset($row['tension_systolique']) && $row['tension_systolique'] !== null ? (int)$row['tension_systolique'] : null,
            isset($row['tension_diastolique']) && $row['tension_diastolique'] !== null ? (int)$row['tension_diastolique'] : null,
            isset($row['pouls']) && $row['pouls'] !== null ? (int)$row['pouls'] : null,
            isset($row['temperature_c']) && $row['temperature_c'] !== null ? (float)$row['temperature_c'] : null,
            isset($row['hemoglobine_g_dl']) && $row['hemoglobine_g_dl'] !== null ? (float)$row['hemoglobine_g_dl'] : null,
            isset($row['poids_kg']) && $row['poids_kg'] !== null ? (float)$row['poids_kg'] : null,
            (string)($row['decision'] ?? 'EN_ATTENTE'),
            (int)($row['saisi_par'] ?? 0),
            isset($row['saisi_le']) ? (string)$row['saisi_le'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'participation_id' => $this->participationId,
            'tension_systolique' => $this->tensionSystolique,
            'tension_diastolique' => $this->tensionDiastolique,
            'pouls' => $this->pouls,
            'temperature_c' => $this->temperatureC,
            'hemoglobine_g_dl' => $this->hemoglobineGDl,
            'poids_kg' => $this->poidsKg,
            'decision' => $this->decision,
            'saisi_par' => $this->saisiPar,
            'saisi_le' => $this->saisiLe,
        ];
    }

    public function getParticipationId(): int { return $this->participationId; }
    public function getTensionSystolique(): ?int { return $this->tensionSystolique; }
    public function getTensionDiastolique(): ?int { return $this->tensionDiastolique; }
    public function getPouls(): ?int { return $this->pouls; }
    public function getTemperatureC(): ?float { return $this->temperatureC; }
    public function getHemoglobineGDl(): ?float { return $this->hemoglobineGDl; }
    public function getPoidsKg(): ?float { return $this->poidsKg; }
    public function getDecision(): string { return $this->decision; }
    public function getSaisiPar(): int { return $this->saisiPar; }
    public function getSaisiLe(): ?string { return $this->saisiLe; }
}
