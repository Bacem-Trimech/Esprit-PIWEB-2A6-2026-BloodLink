<?php
declare(strict_types=1);

final class ParametreResponsable
{
    public function __construct(
        private int $responsableId,
        private string $nomOrganisation = 'BloodLink',
        private string $contactPublic = '',
        private int $capaciteDefaut = 2,
        private int $horizonReservationJours = 60,
        private string $messageAccueil = 'Bienvenue à notre collecte de sang.'
    ) {}

    public static function fromArray(array $row, int $responsableId = 0): self
    {
        return new self(
            (int)($row['responsable_id'] ?? $responsableId),
            (string)($row['nom_organisation'] ?? 'BloodLink'),
            (string)($row['contact_public'] ?? ''),
            (int)($row['capacite_defaut'] ?? 2),
            (int)($row['horizon_reservation_jours'] ?? 60),
            (string)($row['message_accueil'] ?? 'Bienvenue à notre collecte de sang.')
        );
    }

    public function toArray(): array
    {
        return [
            'responsable_id' => $this->responsableId,
            'nom_organisation' => $this->nomOrganisation,
            'contact_public' => $this->contactPublic,
            'capacite_defaut' => $this->capaciteDefaut,
            'horizon_reservation_jours' => $this->horizonReservationJours,
            'message_accueil' => $this->messageAccueil,
        ];
    }

    public function getResponsableId(): int { return $this->responsableId; }
    public function getNomOrganisation(): string { return $this->nomOrganisation; }
    public function getContactPublic(): string { return $this->contactPublic; }
    public function getCapaciteDefaut(): int { return $this->capaciteDefaut; }
    public function getHorizonReservationJours(): int { return $this->horizonReservationJours; }
    public function getMessageAccueil(): string { return $this->messageAccueil; }
}
