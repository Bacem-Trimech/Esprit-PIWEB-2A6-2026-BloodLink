<?php
class AnalyseMedicale {
    private ?int $id;
    private ?string $nom;
    private ?string $description;
    private ?string $typeEchantillon;
    private ?float $prix;
    private ?int $delaiHeures;
    private ?int $aJeun;
    private ?DateTime $dateAjout;

    //Constructor
    public function __construct(?int $id, ?string $nom, ?string $description, ?string $typeEchantillon, ?float $prix, ?int $delaiHeures, ?int $aJeun, ?DateTime $dateAjout) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->typeEchantillon = $typeEchantillon;
        $this->prix = $prix;
        $this->delaiHeures = $delaiHeures;
        $this->aJeun = $aJeun;
        $this->dateAjout = $dateAjout;
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): void { $this->nom = $nom; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getTypeEchantillon(): ?string { return $this->typeEchantillon; }
    public function setTypeEchantillon(?string $typeEchantillon): void { $this->typeEchantillon = $typeEchantillon; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(?float $prix): void { $this->prix = $prix; }

    public function getDelaiHeures(): ?int { return $this->delaiHeures; }
    public function setDelaiHeures(?int $delaiHeures): void { $this->delaiHeures = $delaiHeures; }

    public function getAJeun(): ?int { return $this->aJeun; }
    public function setAJeun(?int $aJeun): void { $this->aJeun = $aJeun; }

    public function getDateAjout(): ?DateTime { return $this->dateAjout; }
    public function setDateAjout(?DateTime $dateAjout): void { $this->dateAjout = $dateAjout; }
}
?>
