<?php
declare(strict_types=1);

final class CompteRenduAnalyse
{
    public const INTERPRETATIONS = ['Normal', 'Anormal'];

    public function __construct(
        private ?int $id = null,
        private int $idAnalyse = 0,
        private ?int $utilisateurId = null,
        private string $nomPatient = '',
        private string $datePrelevement = '',
        private string $resultat = '',
        private string $interpretation = 'Normal',
        private ?string $commentaire = null,
        private string $dateCreation = ''
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (int)($row['id_analyse'] ?? 0),
            isset($row['utilisateur_id']) && $row['utilisateur_id'] !== '' ? (int)$row['utilisateur_id'] : null,
            (string)($row['nom_patient'] ?? ''),
            (string)($row['date_prelevement'] ?? ''),
            (string)($row['resultat'] ?? ''),
            (string)($row['interpretation'] ?? 'Normal'),
            isset($row['commentaire']) && $row['commentaire'] !== '' ? (string)$row['commentaire'] : null,
            (string)($row['date_creation'] ?? date('Y-m-d'))
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'id_analyse' => $this->idAnalyse,
            'utilisateur_id' => $this->utilisateurId,
            'nom_patient' => $this->nomPatient,
            'date_prelevement' => $this->datePrelevement,
            'resultat' => $this->resultat,
            'interpretation' => $this->interpretation,
            'commentaire' => $this->commentaire,
            'date_creation' => $this->dateCreation,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getIdAnalyse(): int { return $this->idAnalyse; }
    public function setIdAnalyse(int $idAnalyse): void { $this->idAnalyse = $idAnalyse; }

    public function getUtilisateurId(): ?int { return $this->utilisateurId; }
    public function setUtilisateurId(?int $utilisateurId): void { $this->utilisateurId = $utilisateurId; }

    public function getNomPatient(): string { return $this->nomPatient; }
    public function setNomPatient(string $nomPatient): void { $this->nomPatient = $nomPatient; }

    public function getDatePrelevement(): string { return $this->datePrelevement; }
    public function setDatePrelevement(string $datePrelevement): void { $this->datePrelevement = $datePrelevement; }

    public function getResultat(): string { return $this->resultat; }
    public function setResultat(string $resultat): void { $this->resultat = $resultat; }

    public function getInterpretation(): string { return $this->interpretation; }
    public function setInterpretation(string $interpretation): void { $this->interpretation = $interpretation; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): void { $this->commentaire = $commentaire; }

    public function getDateCreation(): string { return $this->dateCreation; }
    public function setDateCreation(string $dateCreation): void { $this->dateCreation = $dateCreation; }
}
