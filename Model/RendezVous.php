
<?php
class RendezVous
{
    private ?int $id;
    private ?string $date_rendezvous;
    private ?string $heure_rendezvous;
    private ?string $motif;
    private ?string $statut;
    private ?int $id_praticien;

    public function __construct(
        ?int $id,
        ?string $date_rendezvous,
        ?string $heure_rendezvous,
        ?string $motif,
        ?string $statut,
        ?int $id_praticien
    ) {
        $this->id = $id;
        $this->date_rendezvous = $date_rendezvous;
        $this->heure_rendezvous = $heure_rendezvous;
        $this->motif = $motif;
        $this->statut = $statut;
        $this->id_praticien = $id_praticien;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getDateRendezVous(): ?string { return $this->date_rendezvous; }
    public function setDateRendezVous(?string $date): void { $this->date_rendezvous = $date; }

    public function getHeureRendezVous(): ?string { return $this->heure_rendezvous; }
    public function setHeureRendezVous(?string $heure): void { $this->heure_rendezvous = $heure; }

    public function getMotif(): ?string { return $this->motif; }
    public function setMotif(?string $motif): void { $this->motif = $motif; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }

    public function getIdPraticien(): ?int { return $this->id_praticien; }
    public function setIdPraticien(?int $id): void { $this->id_praticien = $id; }
}
?>
