<?php
declare(strict_types=1);

final class ParticipationCollecte
{
    public function __construct(
        private ?int $id = null,
        private int $collecteId = 0,
        private ?int $creneauId = null,
        private int $donneurId = 0,
        private string $mode = 'EN_LIGNE',
        private string $statut = 'CONFIRMEE',
        private string $codeConfirmation = '',
        private ?string $creeLe = null,
        private ?string $arriveeLe = null,
        private ?string $nom = null,
        private ?string $email = null,
        private ?string $titre = null,
        private ?string $ville = null,
        private ?string $adresse = null,
        private ?string $collecteDebut = null,
        private ?string $creneauDebut = null,
        private ?string $effectueLe = null,
        private ?int $volumeMl = null,
        private ?string $numeroPoche = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (int)($row['collecte_id'] ?? 0),
            isset($row['creneau_id']) && $row['creneau_id'] !== null ? (int)$row['creneau_id'] : null,
            (int)($row['donneur_id'] ?? 0),
            (string)($row['mode'] ?? 'EN_LIGNE'),
            (string)($row['statut'] ?? 'CONFIRMEE'),
            (string)($row['code_confirmation'] ?? ''),
            isset($row['cree_le']) ? (string)$row['cree_le'] : null,
            isset($row['arrivee_le']) ? (string)$row['arrivee_le'] : null,
            isset($row['nom']) ? (string)$row['nom'] : null,
            isset($row['email']) ? (string)$row['email'] : null,
            isset($row['titre']) ? (string)$row['titre'] : null,
            isset($row['ville']) ? (string)$row['ville'] : null,
            isset($row['adresse']) ? (string)$row['adresse'] : null,
            isset($row['collecte_debut']) ? (string)$row['collecte_debut'] : null,
            isset($row['creneau_debut']) ? (string)$row['creneau_debut'] : null,
            isset($row['effectue_le']) ? (string)$row['effectue_le'] : null,
            isset($row['volume_ml']) && $row['volume_ml'] !== null ? (int)$row['volume_ml'] : null,
            isset($row['numero_poche']) ? (string)$row['numero_poche'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'collecte_id' => $this->collecteId,
            'creneau_id' => $this->creneauId,
            'donneur_id' => $this->donneurId,
            'mode' => $this->mode,
            'statut' => $this->statut,
            'code_confirmation' => $this->codeConfirmation,
            'cree_le' => $this->creeLe,
            'arrivee_le' => $this->arriveeLe,
            'nom' => $this->nom,
            'email' => $this->email,
            'titre' => $this->titre,
            'ville' => $this->ville,
            'adresse' => $this->adresse,
            'collecte_debut' => $this->collecteDebut,
            'creneau_debut' => $this->creneauDebut,
            'effectue_le' => $this->effectueLe,
            'volume_ml' => $this->volumeMl,
            'numero_poche' => $this->numeroPoche,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCollecteId(): int { return $this->collecteId; }
    public function setCollecteId(int $collecteId): void { $this->collecteId = $collecteId; }

    public function getCreneauId(): ?int { return $this->creneauId; }
    public function setCreneauId(?int $creneauId): void { $this->creneauId = $creneauId; }

    public function getDonneurId(): int { return $this->donneurId; }
    public function setDonneurId(int $donneurId): void { $this->donneurId = $donneurId; }

    public function getMode(): string { return $this->mode; }
    public function setMode(string $mode): void { $this->mode = $mode; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getCodeConfirmation(): string { return $this->codeConfirmation; }
    public function setCodeConfirmation(string $codeConfirmation): void { $this->codeConfirmation = $codeConfirmation; }

    public function getCreeLe(): ?string { return $this->creeLe; }
    public function getArriveeLe(): ?string { return $this->arriveeLe; }
    public function setArriveeLe(?string $arriveeLe): void { $this->arriveeLe = $arriveeLe; }
}
