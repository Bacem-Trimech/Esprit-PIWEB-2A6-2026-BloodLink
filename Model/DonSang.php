<?php
declare(strict_types=1);

final class DonSang
{
    public function __construct(
        private ?int $id = null,
        private int $participationId = 0,
        private string $effectueLe = '',
        private int $confirmePar = 0,
        private int $volumeMl = 450,
        private string $numeroPoche = ''
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (int)($row['participation_id'] ?? 0),
            (string)($row['effectue_le'] ?? ''),
            (int)($row['confirme_par'] ?? 0),
            (int)($row['volume_ml'] ?? 450),
            (string)($row['numero_poche'] ?? '')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'participation_id' => $this->participationId,
            'effectue_le' => $this->effectueLe,
            'confirme_par' => $this->confirmePar,
            'volume_ml' => $this->volumeMl,
            'numero_poche' => $this->numeroPoche,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getParticipationId(): int { return $this->participationId; }
    public function setParticipationId(int $participationId): void { $this->participationId = $participationId; }

    public function getEffectueLe(): string { return $this->effectueLe; }
    public function setEffectueLe(string $effectueLe): void { $this->effectueLe = $effectueLe; }

    public function getConfirmePar(): int { return $this->confirmePar; }
    public function setConfirmePar(int $confirmePar): void { $this->confirmePar = $confirmePar; }

    public function getVolumeMl(): int { return $this->volumeMl; }
    public function setVolumeMl(int $volumeMl): void { $this->volumeMl = $volumeMl; }

    public function getNumeroPoche(): string { return $this->numeroPoche; }
    public function setNumeroPoche(string $numeroPoche): void { $this->numeroPoche = $numeroPoche; }
}
