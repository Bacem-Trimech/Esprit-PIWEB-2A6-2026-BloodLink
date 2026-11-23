<?php
class CompteRenduAnalyse {
    private ?int $id;
    private ?int $idAnalyse;
    private ?string $nomPatient;
    private ?DateTime $datePrelevement;
    private ?string $resultat;
    private ?string $interpretation;
    private ?string $commentaire;
    private ?DateTime $dateCreation;

    //Constructor
    public function __construct(?int $id, ?int $idAnalyse, ?string $nomPatient, ?DateTime $datePrelevement, ?string $resultat, ?string $interpretation, ?string $commentaire, ?DateTime $dateCreation) {
        $this->id = $id;
        $this->idAnalyse = $idAnalyse;
        $this->nomPatient = $nomPatient;
        $this->datePrelevement = $datePrelevement;
        $this->resultat = $resultat;
        $this->interpretation = $interpretation;
        $this->commentaire = $commentaire;
        $this->dateCreation = $dateCreation;
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getIdAnalyse(): ?int { return $this->idAnalyse; }
    public function setIdAnalyse(?int $idAnalyse): void { $this->idAnalyse = $idAnalyse; }

    public function getNomPatient(): ?string { return $this->nomPatient; }
    public function setNomPatient(?string $nomPatient): void { $this->nomPatient = $nomPatient; }

    public function getDatePrelevement(): ?DateTime { return $this->datePrelevement; }
    public function setDatePrelevement(?DateTime $datePrelevement): void { $this->datePrelevement = $datePrelevement; }

    public function getResultat(): ?string { return $this->resultat; }
    public function setResultat(?string $resultat): void { $this->resultat = $resultat; }

    public function getInterpretation(): ?string { return $this->interpretation; }
    public function setInterpretation(?string $interpretation): void { $this->interpretation = $interpretation; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): void { $this->commentaire = $commentaire; }

    public function getDateCreation(): ?DateTime { return $this->dateCreation; }
    public function setDateCreation(?DateTime $dateCreation): void { $this->dateCreation = $dateCreation; }
}
?>
