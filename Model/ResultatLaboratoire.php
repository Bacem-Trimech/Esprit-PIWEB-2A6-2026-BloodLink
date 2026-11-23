<?php
declare(strict_types=1);

final class ResultatLaboratoire
{
    public function __construct(
        private int $participationId,
        private ?string $groupeConfirme = null,
        private string $statut = 'EN_ATTENTE',
        private string $vih = 'EN_ATTENTE',
        private string $vhb = 'EN_ATTENTE',
        private string $vhc = 'EN_ATTENTE',
        private string $syphilis = 'EN_ATTENTE',
        private string $statutProchainDon = 'EN_COURS',
        private ?string $prochainDonLe = null,
        private int $saisiPar = 0,
        private ?string $saisiLe = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['participation_id'] ?? $row['id'] ?? 0),
            isset($row['groupe_confirme']) && $row['groupe_confirme'] !== '' ? (string)$row['groupe_confirme'] : null,
            (string)($row['statut'] ?? 'EN_ATTENTE'),
            (string)($row['vih'] ?? 'EN_ATTENTE'),
            (string)($row['vhb'] ?? 'EN_ATTENTE'),
            (string)($row['vhc'] ?? 'EN_ATTENTE'),
            (string)($row['syphilis'] ?? 'EN_ATTENTE'),
            (string)($row['statut_prochain_don'] ?? 'EN_COURS'),
            isset($row['prochain_don_le']) && $row['prochain_don_le'] !== '' ? (string)$row['prochain_don_le'] : null,
            (int)($row['saisi_par'] ?? 0),
            isset($row['saisi_le']) ? (string)$row['saisi_le'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'participation_id' => $this->participationId,
            'groupe_confirme' => $this->groupeConfirme,
            'statut' => $this->statut,
            'vih' => $this->vih,
            'vhb' => $this->vhb,
            'vhc' => $this->vhc,
            'syphilis' => $this->syphilis,
            'statut_prochain_don' => $this->statutProchainDon,
            'prochain_don_le' => $this->prochainDonLe,
            'saisi_par' => $this->saisiPar,
            'saisi_le' => $this->saisiLe,
        ];
    }

    public function getParticipationId(): int { return $this->participationId; }
    public function getGroupeConfirme(): ?string { return $this->groupeConfirme; }
    public function getStatut(): string { return $this->statut; }
    public function getVih(): string { return $this->vih; }
    public function getVhb(): string { return $this->vhb; }
    public function getVhc(): string { return $this->vhc; }
    public function getSyphilis(): string { return $this->syphilis; }
    public function getStatutProchainDon(): string { return $this->statutProchainDon; }
    public function getProchainDonLe(): ?string { return $this->prochainDonLe; }
    public function getSaisiPar(): int { return $this->saisiPar; }
    public function getSaisiLe(): ?string { return $this->saisiLe; }
}
