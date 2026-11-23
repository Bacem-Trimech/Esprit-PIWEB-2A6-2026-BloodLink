<?php
declare(strict_types=1);

final class RendezVous
{
    public const STATUTS = ['En attente', 'Confirmé', 'Annulé', 'Terminé'];

    public function __construct(
        private ?int $id = null,
        private string $dateRendezVous = '',
        private string $heureRendezVous = '',
        private string $motif = '',
        private string $statut = 'En attente',
        private int $idPraticien = 0,
        private ?int $utilisateurId = null,
        private ?string $patientNom = null,
        private ?string $patientTelephone = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id_rendezvous']) ? (int)$row['id_rendezvous'] : (isset($row['id']) ? (int)$row['id'] : null),
            (string)($row['date_rendezvous'] ?? ''),
            (string)($row['heure_rendezvous'] ?? ''),
            (string)($row['motif'] ?? ''),
            (string)($row['statut'] ?? 'En attente'),
            (int)($row['id_praticien'] ?? 0),
            isset($row['utilisateur_id']) && $row['utilisateur_id'] !== '' ? (int)$row['utilisateur_id'] : null,
            isset($row['patient_nom']) && $row['patient_nom'] !== '' ? (string)$row['patient_nom'] : null,
            isset($row['patient_telephone']) && $row['patient_telephone'] !== '' ? (string)$row['patient_telephone'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id_rendezvous' => $this->id,
            'date_rendezvous' => $this->dateRendezVous,
            'heure_rendezvous' => $this->heureRendezVous,
            'motif' => $this->motif,
            'statut' => $this->statut,
            'id_praticien' => $this->idPraticien,
            'utilisateur_id' => $this->utilisateurId,
            'patient_nom' => $this->patientNom,
            'patient_telephone' => $this->patientTelephone,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getDateRendezVous(): string { return $this->dateRendezVous; }
    public function setDateRendezVous(string $date): void { $this->dateRendezVous = $date; }

    public function getHeureRendezVous(): string { return $this->heureRendezVous; }
    public function setHeureRendezVous(string $heure): void { $this->heureRendezVous = $heure; }

    public function getMotif(): string { return $this->motif; }
    public function setMotif(string $motif): void { $this->motif = $motif; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getIdPraticien(): int { return $this->idPraticien; }
    public function setIdPraticien(int $id): void { $this->idPraticien = $id; }

    public function getUtilisateurId(): ?int { return $this->utilisateurId; }
    public function setUtilisateurId(?int $utilisateurId): void { $this->utilisateurId = $utilisateurId; }

    public function getPatientNom(): ?string { return $this->patientNom; }
    public function setPatientNom(?string $patientNom): void { $this->patientNom = $patientNom; }

    public function getPatientTelephone(): ?string { return $this->patientTelephone; }
    public function setPatientTelephone(?string $patientTelephone): void { $this->patientTelephone = $patientTelephone; }
}
