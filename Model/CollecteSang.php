<?php
declare(strict_types=1);

final class CollecteSang
{
    public const MAX_GALERIE_IMAGES = 4;

    public const IMAGES_PREDEFINIES = [
        'public/assets/img/collecte-centre.svg' => 'Centre de transfusion (Classique)',
        'public/assets/img/collecte-mobile.svg' => 'Unité mobile & Clinique',
        'public/assets/img/collecte-campus.svg' => 'Journée solidaire & Campus',
    ];

    public function __construct(
        private ?int $id = null,
        private string $titre = '',
        private string $adresse = '',
        private string $ville = '',
        private string $debut = '',
        private string $fin = '',
        private string $statut = 'BROUILLON',
        private int $organisateurId = 0,
        private ?string $imageUrl = null,
        private array $galerie = [],
        private ?string $organisateur = null,
        private ?string $nomOrganisation = null,
        private ?string $contactPublic = null,
        private ?string $messageAccueil = null,
        private int $participations = 0,
        private int $dons = 0,
        private int $nbCreneaux = 0,
        private int $capaciteTotale = 0,
        private int $placesPrises = 0,
        private ?string $horairesResume = null
    ) {}

    public static function fromArray(array $row): self
    {
        $id = isset($row['id']) ? (int)$row['id'] : null;
        $defaultImg = ($id && $id % 2 === 0)
            ? 'public/assets/img/collecte-mobile.svg'
            : 'public/assets/img/collecte-centre.svg';
        $img = !empty($row['image_url']) ? (string)$row['image_url'] : $defaultImg;

        $galerie = [];
        if (!empty($row['galerie_json'])) {
            $decoded = json_decode((string)$row['galerie_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $gItem) {
                    if (is_string($gItem) && $gItem !== '') {
                        $galerie[] = $gItem;
                    }
                }
            }
        }
        if (empty($galerie)) {
            $galerie = [
                $img,
                'public/assets/img/collecte-mobile.svg',
                'public/assets/img/collecte-campus.svg',
            ];
            $galerie = array_values(array_unique($galerie));
        }
        $galerie = array_slice($galerie, 0, self::MAX_GALERIE_IMAGES);

        return new self(
            $id,
            (string)($row['titre'] ?? ''),
            (string)($row['adresse'] ?? ''),
            (string)($row['ville'] ?? ''),
            (string)($row['debut'] ?? ''),
            (string)($row['fin'] ?? ''),
            (string)($row['statut'] ?? 'BROUILLON'),
            (int)($row['organisateur_id'] ?? 0),
            $img,
            $galerie,
            isset($row['organisateur']) ? (string)$row['organisateur'] : null,
            isset($row['nom_organisation']) ? (string)$row['nom_organisation'] : null,
            isset($row['contact_public']) ? (string)$row['contact_public'] : null,
            isset($row['message_accueil']) ? (string)$row['message_accueil'] : null,
            (int)($row['participations'] ?? 0),
            (int)($row['dons'] ?? 0),
            (int)($row['nb_creneaux'] ?? 0),
            (int)($row['capacite_totale'] ?? 0),
            (int)($row['places_prises'] ?? 0),
            isset($row['horaires_resume']) ? (string)$row['horaires_resume'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'debut' => $this->debut,
            'fin' => $this->fin,
            'statut' => $this->statut,
            'organisateur_id' => $this->organisateurId,
            'image_url' => $this->imageUrl,
            'galerie' => $this->galerie,
            'galerie_json' => json_encode(array_slice($this->galerie, 0, self::MAX_GALERIE_IMAGES), JSON_UNESCAPED_SLASHES),
            'organisateur' => $this->organisateur,
            'nom_organisation' => $this->nomOrganisation,
            'contact_public' => $this->contactPublic,
            'message_accueil' => $this->messageAccueil,
            'participations' => $this->participations,
            'dons' => $this->dons,
            'nb_creneaux' => $this->nbCreneaux,
            'capacite_totale' => $this->capaciteTotale,
            'places_prises' => $this->placesPrises,
            'places_libres' => max(0, $this->capaciteTotale - $this->placesPrises),
            'horaires_resume' => $this->horairesResume,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): void { $this->titre = $titre; }

    public function getAdresse(): string { return $this->adresse; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }

    public function getVille(): string { return $this->ville; }
    public function setVille(string $ville): void { $this->ville = $ville; }

    public function getDebut(): string { return $this->debut; }
    public function setDebut(string $debut): void { $this->debut = $debut; }

    public function getFin(): string { return $this->fin; }
    public function setFin(string $fin): void { $this->fin = $fin; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getOrganisateurId(): int { return $this->organisateurId; }
    public function setOrganisateurId(int $organisateurId): void { $this->organisateurId = $organisateurId; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }

    public function getGalerie(): array { return $this->galerie; }
    public function setGalerie(array $galerie): void { $this->galerie = array_slice($galerie, 0, self::MAX_GALERIE_IMAGES); }
}
