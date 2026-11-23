<?php
declare(strict_types=1);

final class CollecteController
{
    public function publiques(): array
    {
        $q = connexion()->query("
            SELECT c.*,
                   u.nom AS organisateur,
                   COALESCE(r.nom_organisation, 'BloodLink') AS nom_organisation,
                   r.message_accueil,
                   (SELECT COUNT(*) FROM creneaux cr WHERE cr.collecte_id=c.id AND cr.statut='OUVERT') AS nb_creneaux,
                   (SELECT COALESCE(SUM(cr.capacite),0) FROM creneaux cr WHERE cr.collecte_id=c.id AND cr.statut='OUVERT') AS capacite_totale,
                   (SELECT COUNT(*) FROM participations p JOIN creneaux cr ON cr.id=p.creneau_id WHERE cr.collecte_id=c.id AND cr.statut='OUVERT' AND p.statut IN ('CONFIRMEE','ARRIVEE')) AS places_prises,
                   (SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(cr.debut,'%H:%i'),'-',DATE_FORMAT(cr.fin,'%H:%i')) SEPARATOR ',') FROM creneaux cr WHERE cr.collecte_id=c.id AND cr.statut='OUVERT') AS horaires_resume
            FROM collectes c
            JOIN utilisateurs u ON u.id=c.organisateur_id
            LEFT JOIN parametres_responsable r ON r.responsable_id=u.id
            WHERE c.statut='PUBLIEE' AND c.fin>=NOW()
            ORDER BY c.debut
        ");
        return array_map(static fn(array $row): array => CollecteSang::fromArray($row)->toArray(), $q->fetchAll());
    }

    public function detail(int $id): ?array
    {
        $q = connexion()->prepare("
            SELECT c.*,
                   u.nom AS organisateur,
                   COALESCE(r.nom_organisation, 'BloodLink') AS nom_organisation,
                   r.contact_public,
                   r.message_accueil,
                   (SELECT COUNT(*) FROM creneaux cr WHERE cr.collecte_id=c.id AND cr.statut='OUVERT') AS nb_creneaux,
                   (SELECT COALESCE(SUM(cr.capacite),0) FROM creneaux cr WHERE cr.collecte_id=c.id AND cr.statut='OUVERT') AS capacite_totale,
                   (SELECT COUNT(*) FROM participations p JOIN creneaux cr ON cr.id=p.creneau_id WHERE cr.collecte_id=c.id AND cr.statut='OUVERT' AND p.statut IN ('CONFIRMEE','ARRIVEE')) AS places_prises
            FROM collectes c
            JOIN utilisateurs u ON u.id=c.organisateur_id
            LEFT JOIN parametres_responsable r ON r.responsable_id=u.id
            WHERE c.id=?
        ");
        $q->execute([$id]);
        $item = $q->fetch();
        if (!$item) return null;
        $collecte = CollecteSang::fromArray($item);
        if ($collecte->getStatut() !== 'PUBLIEE' && !in_array(utilisateur()['role'] ?? '', ['RESPONSABLE','ADMIN'], true)) return null;
        return $collecte->toArray();
    }

    public function creneaux(int $id): array
    {
        $q = connexion()->prepare("SELECT cr.*,COUNT(CASE WHEN p.statut IN ('CONFIRMEE','ARRIVEE') THEN 1 END) AS places_prises FROM creneaux cr LEFT JOIN participations p ON p.creneau_id=cr.id WHERE cr.collecte_id=? GROUP BY cr.id ORDER BY cr.debut");
        $q->execute([$id]);
        return array_map(static fn(array $row): array => CreneauCollecte::fromArray($row)->toArray(), $q->fetchAll());
    }

    public function mesParticipations(int $id): array
    {
        $q = connexion()->prepare('SELECT p.*,c.titre,c.ville,c.adresse,c.debut AS collecte_debut,cr.debut AS creneau_debut,d.effectue_le,d.volume_ml,d.numero_poche FROM participations p JOIN collectes c ON c.id=p.collecte_id LEFT JOIN creneaux cr ON cr.id=p.creneau_id LEFT JOIN dons_sang d ON d.participation_id=p.id WHERE p.donneur_id=? ORDER BY p.cree_le DESC');
        $q->execute([$id]);
        return array_map(static fn(array $row): array => ParticipationCollecte::fromArray($row)->toArray(), $q->fetchAll());
    }

    public function etatEligibiliteDonneur(int $donneurId): array
    {
        $db = connexion();
        $q = $db->prepare("
            SELECT d.effectue_le, d.volume_ml, d.numero_poche, p.collecte_id, c.titre AS collecte_titre, c.ville AS collecte_ville
            FROM dons_sang d
            JOIN participations p ON p.id = d.participation_id
            JOIN collectes c ON c.id = p.collecte_id
            WHERE p.donneur_id = ?
            ORDER BY d.effectue_le DESC
        ");
        $q->execute([$donneurId]);
        $dons = $q->fetchAll();

        $qAvis = $db->prepare("
            SELECT l.statut_prochain_don, l.prochain_don_le, l.groupe_confirme
            FROM resultats_laboratoire l
            JOIN participations p ON p.id = l.participation_id
            JOIN dons_sang d ON d.participation_id = p.id
            WHERE p.donneur_id = ?
            ORDER BY d.effectue_le DESC
            LIMIT 1
        ");
        $qAvis->execute([$donneurId]);
        $avis = $qAvis->fetch() ?: [];

        $dernierDon = $dons[0] ?? null;
        $prochainPossible = null;
        if ($dernierDon && !empty($dernierDon['effectue_le'])) {
            $prochainPossible = (new DateTimeImmutable((string)$dernierDon['effectue_le']))->modify('+12 weeks');
        }
        if (!empty($avis['prochain_don_le'])) {
            $dateCentre = new DateTimeImmutable((string)$avis['prochain_don_le'] . ' 09:00:00');
            if (!$prochainPossible || $dateCentre > $prochainPossible) {
                $prochainPossible = $dateCentre;
            }
        }

        $now = new DateTimeImmutable('now');
        $enDelai = $prochainPossible !== null && $prochainPossible > $now;
        $secondesRestantes = $enDelai ? max(0, $prochainPossible->getTimestamp() - $now->getTimestamp()) : 0;
        $joursRestants = $enDelai ? (int)ceil($secondesRestantes / 86400) : 0;

        $collectesDonneesIds = [];
        $volumeTotal = 0;
        foreach ($dons as $d) {
            $collectesDonneesIds[(int)$d['collecte_id']] = (string)$d['effectue_le'];
            $volumeTotal += (int)($d['volume_ml'] ?? 0);
        }

        return [
            'total_dons' => count($dons),
            'volume_total_ml' => $volumeTotal,
            'dernier_don_le' => $dernierDon['effectue_le'] ?? null,
            'dernier_don_titre' => $dernierDon['collecte_titre'] ?? null,
            'dernier_don_ville' => $dernierDon['collecte_ville'] ?? null,
            'dernier_don_collecte_id' => isset($dernierDon['collecte_id']) ? (int)$dernierDon['collecte_id'] : null,
            'prochain_don_le' => $prochainPossible ? $prochainPossible->format('Y-m-d H:i:s') : null,
            'prochain_don_iso' => $prochainPossible ? $prochainPossible->format(DATE_ATOM) : null,
            'en_delai_12_semaines' => $enDelai,
            'jours_restants' => $joursRestants,
            'statut_centre' => (string)($avis['statut_prochain_don'] ?? 'EN_COURS'),
            'groupe_confirme' => $avis['groupe_confirme'] ?? null,
            'collectes_donnees' => $collectesDonneesIds,
        ];
    }

    public function administrables(array $user): array
    {
        $condSain = "l.statut='VALIDE' AND l.groupe_confirme IS NOT NULL AND l.vih='NON_REACTIF' AND l.vhb='NON_REACTIF' AND l.vhc='NON_REACTIF' AND l.syphilis='NON_REACTIF'";
        $sql = "SELECT c.*,
                       COUNT(DISTINCT CASE WHEN p.statut NOT IN ('ANNULEE','ABSENTE') THEN p.id END) AS participations,
                       COUNT(DISTINCT d.id) AS dons,
                       COALESCE(SUM(d.volume_ml),0) AS volume_ml,
                       COALESCE(SUM(CASE WHEN {$condSain} THEN d.volume_ml ELSE 0 END),0) AS volume_sain_ml
                FROM collectes c
                LEFT JOIN participations p ON p.collecte_id=c.id
                LEFT JOIN dons_sang d ON d.participation_id=p.id
                LEFT JOIN resultats_laboratoire l ON l.participation_id=p.id";
        if ($user['role'] === 'RESPONSABLE') {
            $q = connexion()->prepare($sql . ' WHERE c.organisateur_id=? GROUP BY c.id ORDER BY c.debut DESC');
            $q->execute([$user['id']]);
        } else {
            $q = connexion()->query($sql . ' GROUP BY c.id ORDER BY c.debut DESC');
        }
        $rows = $q->fetchAll();

        $donsRows = connexion()->query("
            SELECT p.collecte_id, d.volume_ml,
                   l.groupe_confirme, l.statut AS labo_statut, l.vih, l.vhb, l.vhc, l.syphilis
            FROM dons_sang d
            JOIN participations p ON p.id = d.participation_id
            LEFT JOIN resultats_laboratoire l ON l.participation_id = p.id
        ")->fetchAll();

        $groupesMap = [];
        foreach ($donsRows as $dr) {
            $cid = (int)$dr['collecte_id'];
            $vol = (int)$dr['volume_ml'];
            $sain = ($dr['labo_statut'] === 'VALIDE')
                && !empty($dr['groupe_confirme'])
                && $dr['vih'] === 'NON_REACTIF'
                && $dr['vhb'] === 'NON_REACTIF'
                && $dr['vhc'] === 'NON_REACTIF'
                && $dr['syphilis'] === 'NON_REACTIF';
            $ecarte = ($dr['labo_statut'] === 'CONTACT_CENTRE')
                || in_array('A_CONFIRMER', [$dr['vih'] ?? '', $dr['vhb'] ?? '', $dr['vhc'] ?? '', $dr['syphilis'] ?? ''], true);

            if ($sain) {
                $key = (string)$dr['groupe_confirme'];
                $etat = 'SAIN';
            } elseif ($ecarte) {
                $key = 'Écarté (contrôle)';
                $etat = 'ECARTE';
            } else {
                $key = 'Groupe inconnu / En attente';
                $etat = 'INCONNU';
            }
            if (!isset($groupesMap[$cid][$key])) {
                $groupesMap[$cid][$key] = ['groupe' => $key, 'etat' => $etat, 'poches' => 0, 'volume_ml' => 0];
            }
            $groupesMap[$cid][$key]['poches'] += DonSang::compterPoches($vol);
            $groupesMap[$cid][$key]['volume_ml'] += $vol;
        }

        return array_map(static function (array $row) use ($groupesMap): array {
            $arr = CollecteSang::fromArray($row)->toArray();
            $cid = (int)$row['id'];
            $volTotal = (int)($row['volume_ml'] ?? 0);
            $volSain = (int)($row['volume_sain_ml'] ?? 0);
            $arr['volume_ml'] = $volTotal;
            $arr['volume_sain_ml'] = $volSain;
            $arr['volume_inconnu_ml'] = max(0, $volTotal - $volSain);
            $arr['groupes'] = array_values($groupesMap[$cid] ?? []);
            return $arr;
        }, $rows);
    }

    private function enregistrerFichierImage(array $file, array &$errors): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $errors[] = 'Chaque image téléversée ne doit pas dépasser 5 Mo.';
            return null;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($allowedMimes[$mime])) {
            $errors[] = 'Format d’image non pris en charge (utilisez JPG, PNG, WEBP ou GIF).';
            return null;
        }
        $uploadDir = dirname(__DIR__) . '/public/uploads/collectes';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        $filename = 'collecte-' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
        $destPath = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $errors[] = 'Impossible d’enregistrer l’image téléversée.';
            return null;
        }
        return 'public/uploads/collectes/' . $filename;
    }

    public function sauverCollecte(array $post, ?int $id = null): array
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $titre = trim((string)($post['titre'] ?? ''));
        $adresse = trim((string)($post['adresse'] ?? ''));
        $ville = trim((string)($post['ville'] ?? ''));
        $debut = str_replace('T', ' ', trim((string)($post['debut'] ?? '')));
        $fin = str_replace('T', ' ', trim((string)($post['fin'] ?? '')));
        $statut = (string)($post['statut'] ?? '');
        $imageChoix = trim((string)($post['image_choix'] ?? 'public/assets/img/collecte-centre.svg'));
        $errors = [];
        if (mb_strlen($titre) < 3 || mb_strlen($titre) > 160) $errors[] = 'Titre invalide (3 à 160 caractères).';
        if (mb_strlen($adresse) < 5 || mb_strlen($adresse) > 255) $errors[] = 'Adresse invalide (5 à 255 caractères).';
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) $errors[] = 'Ville invalide (2 à 100 caractères).';
        if (!$debut || !$fin || !strtotime($debut) || !strtotime($fin) || strtotime($fin) <= strtotime($debut)) {
            $errors[] = 'Les dates de la collecte sont invalides (la fin doit être après le début).';
        }
        if (!in_array($statut, ['BROUILLON','PUBLIEE','CLOTUREE'], true)) $errors[] = 'Statut invalide.';

        $imageExistante = null;
        $galerieExistante = [];
        if ($id) {
            $this->autoriserCollecte($id, $user);
            $qImg = connexion()->prepare('SELECT image_url, galerie_json FROM collectes WHERE id=?');
            $qImg->execute([$id]);
            $rowExist = $qImg->fetch() ?: [];
            $imageExistante = !empty($rowExist['image_url']) ? (string)$rowExist['image_url'] : null;
            if (!empty($rowExist['galerie_json'])) {
                $dec = json_decode((string)$rowExist['galerie_json'], true);
                if (is_array($dec)) $galerieExistante = array_values(array_filter($dec, 'is_string'));
            }

            if (!$errors) {
                $q = connexion()->prepare('SELECT MIN(debut) AS min_debut, MAX(fin) AS max_fin FROM creneaux WHERE collecte_id=?');
                $q->execute([$id]);
                $bounds = $q->fetch();
                if ($bounds && $bounds['min_debut'] && (strtotime($debut) > strtotime($bounds['min_debut']) || strtotime($fin) < strtotime($bounds['max_fin']))) {
                    $errors[] = 'Les horaires de la collecte doivent englober tous les créneaux déjà créés.';
                }
            }
        }

        $imageUrl = array_key_exists($imageChoix, CollecteSang::IMAGES_PREDEFINIES)
            ? $imageChoix
            : ($imageExistante ?: 'public/assets/img/collecte-centre.svg');

        if ($imageChoix === 'CONSERVER' && $imageExistante) {
            $imageUrl = $imageExistante;
        }

        // 1. Téléversement de l'image principale de la carte (1 image)
        if (!empty($_FILES['image_fichier']['name']) && ($_FILES['image_fichier']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $uploadedCover = $this->enregistrerFichierImage($_FILES['image_fichier'], $errors);
            if ($uploadedCover !== null) {
                $imageUrl = $uploadedCover;
            }
        }

        // 2. Téléversement de la galerie de la page détail (jusqu'à 4 images max)
        $galerieFinale = $galerieExistante;
        if (!empty($_FILES['galerie_fichiers']['name']) && is_array($_FILES['galerie_fichiers']['name'])) {
            $nouvellesPhotos = [];
            $names = $_FILES['galerie_fichiers']['name'];
            $countFiles = count(array_filter($names, static fn($n): bool => (string)$n !== ''));
            if ($countFiles > CollecteSang::MAX_GALERIE_IMAGES) {
                $errors[] = 'Vous pouvez téléverser au maximum ' . CollecteSang::MAX_GALERIE_IMAGES . ' photos pour la galerie de la page détail.';
            } elseif (!$errors && $countFiles > 0) {
                for ($i = 0, $n = count($names); $i < $n; $i++) {
                    if ($names[$i] === '' || ($_FILES['galerie_fichiers']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $singleFile = [
                        'name' => $_FILES['galerie_fichiers']['name'][$i],
                        'type' => $_FILES['galerie_fichiers']['type'][$i] ?? '',
                        'tmp_name' => $_FILES['galerie_fichiers']['tmp_name'][$i] ?? '',
                        'error' => $_FILES['galerie_fichiers']['error'][$i] ?? UPLOAD_ERR_OK,
                        'size' => $_FILES['galerie_fichiers']['size'][$i] ?? 0,
                    ];
                    $path = $this->enregistrerFichierImage($singleFile, $errors);
                    if ($path !== null) {
                        $nouvellesPhotos[] = $path;
                    }
                }
                if (!empty($nouvellesPhotos)) {
                    $galerieFinale = array_slice($nouvellesPhotos, 0, CollecteSang::MAX_GALERIE_IMAGES);
                }
            }
        }
        if (empty($galerieFinale)) {
            $galerieFinale = array_slice(array_values(array_unique([
                $imageUrl,
                'public/assets/img/collecte-mobile.svg',
                'public/assets/img/collecte-campus.svg',
            ])), 0, CollecteSang::MAX_GALERIE_IMAGES);
        }

        if ($errors) return $errors;

        $collecte = new CollecteSang(
            $id,
            $titre,
            $adresse,
            $ville,
            date('Y-m-d H:i:s', strtotime($debut)),
            date('Y-m-d H:i:s', strtotime($fin)),
            $statut,
            (int)$user['id'],
            $imageUrl,
            $galerieFinale
        );

        $galerieJson = json_encode($collecte->getGalerie(), JSON_UNESCAPED_SLASHES);

        if ($collecte->getId()) {
            $q = connexion()->prepare('UPDATE collectes SET titre=?,adresse=?,ville=?,debut=?,fin=?,statut=?,image_url=?,galerie_json=? WHERE id=?');
            $q->execute([
                $collecte->getTitre(),
                $collecte->getAdresse(),
                $collecte->getVille(),
                $collecte->getDebut(),
                $collecte->getFin(),
                $collecte->getStatut(),
                $collecte->getImageUrl(),
                $galerieJson,
                $collecte->getId(),
            ]);
        } else {
            $q = connexion()->prepare('INSERT INTO collectes (titre,adresse,ville,debut,fin,statut,image_url,galerie_json,organisateur_id) VALUES (?,?,?,?,?,?,?,?,?)');
            $q->execute([
                $collecte->getTitre(),
                $collecte->getAdresse(),
                $collecte->getVille(),
                $collecte->getDebut(),
                $collecte->getFin(),
                $collecte->getStatut(),
                $collecte->getImageUrl(),
                $galerieJson,
                $collecte->getOrganisateurId(),
            ]);
        }
        return [];
    }

    public function autoriserCollecte(int $id, array $user): void
    {
        $q = connexion()->prepare('SELECT organisateur_id FROM collectes WHERE id=?');
        $q->execute([$id]);
        $owner = $q->fetchColumn();
        if (!$owner || ($user['role'] !== 'ADMIN' && (int)$owner !== (int)$user['id'])) {
            throw new RuntimeException('Collecte introuvable ou accès refusé.');
        }
    }

    public function sauverCreneau(array $post): array
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $collecteId = filter_var($post['collecte_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$collecteId) return ['Collecte invalide.'];
        $this->autoriserCollecte((int)$collecteId, $user);
        $debut = str_replace('T', ' ', trim((string)($post['debut'] ?? '')));
        $fin = str_replace('T', ' ', trim((string)($post['fin'] ?? '')));
        $capacite = filter_var($post['capacite'] ?? null, FILTER_VALIDATE_INT);
        $q = connexion()->prepare('SELECT debut,fin,statut FROM collectes WHERE id=?');
        $q->execute([$collecteId]);
        $collecte = $q->fetch();
        if (!$collecte) return ['Collecte introuvable.'];
        if ($collecte['statut'] === 'CLOTUREE') return ['Impossible d’ajouter un créneau à une collecte clôturée.'];
        if (!$capacite || $capacite < 1 || $capacite > 100 || !strtotime($debut) || !strtotime($fin) || strtotime($debut) < strtotime($collecte['debut']) || strtotime($fin) > strtotime($collecte['fin']) || strtotime($fin) <= strtotime($debut)) {
            return ['Créneau invalide ou hors des horaires de collecte.'];
        }
        $dup = connexion()->prepare('SELECT COUNT(*) FROM creneaux WHERE collecte_id=? AND debut=? AND fin=?');
        $dup->execute([$collecteId, date('Y-m-d H:i:s', strtotime($debut)), date('Y-m-d H:i:s', strtotime($fin))]);
        if ($dup->fetchColumn()) {
            return ['Un créneau identique existe déjà pour cette collecte.'];
        }

        $creneau = new CreneauCollecte(
            null,
            (int)$collecteId,
            date('Y-m-d H:i:s', strtotime($debut)),
            date('Y-m-d H:i:s', strtotime($fin)),
            (int)$capacite,
            'OUVERT'
        );

        $q = connexion()->prepare('INSERT INTO creneaux (collecte_id,debut,fin,capacite,statut) VALUES (?,?,?,?,?)');
        $q->execute([
            $creneau->getCollecteId(),
            $creneau->getDebut(),
            $creneau->getFin(),
            $creneau->getCapacite(),
            $creneau->getStatut(),
        ]);
        return [];
    }

    public function reserver(int $creneauId): void
    {
        $user = exigerConnexion();
        verifierCsrf();
        if ($user['role'] !== 'DONNEUR') throw new RuntimeException('La réservation est réservée aux comptes donneurs. Utilisez un compte donneur pour prendre un créneau.');
        if (!(int)$user['profil_complet']) throw new RuntimeException('Complétez votre profil donneur avant de réserver.');
        $db = connexion();
        $db->beginTransaction();
        try {
            $q = $db->prepare("SELECT cr.*,c.statut AS collecte_statut,c.fin AS collecte_fin,COALESCE(r.horizon_reservation_jours,60) AS horizon_jours FROM creneaux cr JOIN collectes c ON c.id=cr.collecte_id LEFT JOIN parametres_responsable r ON r.responsable_id=c.organisateur_id WHERE cr.id=? FOR UPDATE");
            $q->execute([$creneauId]);
            $cr = $q->fetch();
            if (!$cr || $cr['statut'] !== 'OUVERT' || $cr['collecte_statut'] !== 'PUBLIEE' || strtotime($cr['debut']) <= time()) {
                throw new RuntimeException('Ce créneau n’est plus disponible.');
            }
            $horizon = (int)$cr['horizon_jours'];
            if ($horizon > 0 && strtotime($cr['debut']) > time() + $horizon * 86400) {
                throw new RuntimeException('Ce créneau est au-delà de l’horizon de réservation autorisé.');
            }
            $q = $db->prepare("SELECT COUNT(*) FROM participations WHERE creneau_id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
            $q->execute([$creneauId]);
            if ((int)$q->fetchColumn() >= (int)$cr['capacite']) throw new RuntimeException('Ce créneau est complet.');
            $this->verifierDelaiDon((int)$user['id'], (string)$cr['debut']);
            $q = $db->prepare("SELECT COUNT(*) FROM participations WHERE collecte_id=? AND donneur_id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
            $q->execute([$cr['collecte_id'], $user['id']]);
            if ($q->fetchColumn()) throw new RuntimeException('Vous participez déjà à cette collecte.');

            $participation = new ParticipationCollecte(
                null,
                (int)$cr['collecte_id'],
                $creneauId,
                (int)$user['id'],
                'EN_LIGNE',
                'CONFIRMEE',
                strtoupper(bin2hex(random_bytes(6)))
            );

            $q = $db->prepare('INSERT INTO participations (collecte_id,creneau_id,donneur_id,mode,statut,code_confirmation) VALUES (?,?,?,?,?,?)');
            $q->execute([
                $participation->getCollecteId(),
                $participation->getCreneauId(),
                $participation->getDonneurId(),
                $participation->getMode(),
                $participation->getStatut(),
                $participation->getCodeConfirmation(),
            ]);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollBack();
            throw $error;
        }
    }

    public function annuler(int $id): void
    {
        $user = exigerConnexion();
        verifierCsrf();
        $q = connexion()->prepare("UPDATE participations p JOIN collectes c ON c.id=p.collecte_id SET p.statut='ANNULEE' WHERE p.id=? AND p.donneur_id=? AND p.statut='CONFIRMEE' AND c.debut>NOW()");
        $q->execute([$id, $user['id']]);
        if (!$q->rowCount()) throw new RuntimeException('Cette réservation ne peut pas être annulée.');
    }

    public function participationsCollecte(int $collecteId, array $user): array
    {
        $this->autoriserCollecte($collecteId, $user);
        $q = connexion()->prepare("
            SELECT p.*, u.nom, u.email, u.telephone, u.ville, u.groupe_sanguin,
                   cr.debut AS creneau_debut,
                   d.effectue_le, d.volume_ml, d.numero_poche,
                   (SELECT COUNT(*) FROM dons_sang d2 JOIN participations p2 ON p2.id=d2.participation_id WHERE p2.donneur_id=p.donneur_id) AS total_dons_compte,
                   (SELECT MAX(d3.effectue_le) FROM dons_sang d3 JOIN participations p3 ON p3.id=d3.participation_id WHERE p3.donneur_id=p.donneur_id AND p3.collecte_id<>p.collecte_id) AS dernier_don_autre_le
            FROM participations p
            JOIN utilisateurs u ON u.id=p.donneur_id
            LEFT JOIN creneaux cr ON cr.id=p.creneau_id
            LEFT JOIN dons_sang d ON d.participation_id=p.id
            WHERE p.collecte_id=? AND p.statut NOT IN ('ANNULEE','ABSENTE')
            ORDER BY p.cree_le DESC
        ");
        $q->execute([$collecteId]);
        $rows = $q->fetchAll();
        $now = new DateTimeImmutable('now');

        return array_map(static function (array $row) use ($now): array {
            $arr = ParticipationCollecte::fromArray($row)->toArray();
            $arr['telephone'] = $row['telephone'] ?? null;
            $arr['groupe_sanguin'] = $row['groupe_sanguin'] ?? null;
            $arr['total_dons_compte'] = (int)($row['total_dons_compte'] ?? 0);
            $arr['dernier_don_autre_le'] = $row['dernier_don_autre_le'] ?? null;

            $refDon = $row['effectue_le'] ?: $row['dernier_don_autre_le'];
            $finDelai = $refDon ? (new DateTimeImmutable((string)$refDon))->modify('+12 weeks') : null;
            $arr['prochain_don_possible_le'] = $finDelai ? $finDelai->format('Y-m-d H:i:s') : null;
            $arr['en_delai_12_semaines'] = $finDelai !== null && $finDelai > $now;
            $arr['jours_restants_delai'] = ($finDelai && $finDelai > $now)
                ? (int)ceil(($finDelai->getTimestamp() - $now->getTimestamp()) / 86400)
                : 0;
            return $arr;
        }, $rows);
    }

    private function normaliserDateHeure(?string $valeur): string
    {
        $clean = str_replace('T', ' ', trim((string)$valeur));
        if ($clean !== '' && strtotime($clean)) {
            return date('Y-m-d H:i:s', strtotime($clean));
        }
        return date('Y-m-d H:i:s');
    }

    public function enregistrerSurPlace(int $collecteId, string $identifiant, ?string $dateHeure = null): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserCollecte($collecteId, $user);
        $q = connexion()->prepare('SELECT debut,statut FROM collectes WHERE id=?');
        $q->execute([$collecteId]);
        $collecte = $q->fetch();
        if (!$collecte || $collecte['statut'] === 'CLOTUREE') {
            throw new RuntimeException('Impossible d’enregistrer une arrivée sur une collecte clôturée.');
        }

        $arriveeLe = $this->normaliserDateHeure($dateHeure);
        $identifiant = trim($identifiant);
        if (preg_match('/^[A-Fa-f0-9]{12}$/D', $identifiant)) {
            $code = strtoupper($identifiant);
            $q = connexion()->prepare('SELECT id,collecte_id,statut FROM participations WHERE code_confirmation=?');
            $q->execute([$code]);
            $reservation = $q->fetch();
            if (!$reservation) throw new RuntimeException('Code de réservation introuvable. Vérifiez les 12 caractères.');
            if ((int)$reservation['collecte_id'] !== $collecteId) throw new RuntimeException('Cette réservation appartient à une autre collecte.');
            if ($reservation['statut'] === 'CONFIRMEE') {
                $this->arriver((int)$reservation['id'], $arriveeLe);
                return;
            }
            throw new RuntimeException('Cette réservation est déjà arrivée, annulée ou terminée.');
        }
        if (ctype_digit($identifiant)) {
            $donneurId = (int)$identifiant;
        } elseif (preg_match('/^[A-Fa-f0-9]{48}$/D', $identifiant)) {
            $q = connexion()->prepare("SELECT id FROM utilisateurs WHERE qr_identifiant=? AND role='DONNEUR' AND actif=1");
            $q->execute([strtolower($identifiant)]);
            $donneurId = (int)$q->fetchColumn();
        } else {
            throw new RuntimeException('Saisissez un identifiant donneur, un code de réservation ou un code QR valide.');
        }

        $q = connexion()->prepare("SELECT id,actif,invitation_hash,invitation_expire FROM utilisateurs WHERE id=? AND role='DONNEUR'");
        $q->execute([$donneurId]);
        $donneur = $q->fetch();
        if (!$donneur) {
            throw new RuntimeException('Compte donneur introuvable. Créez d’abord son compte.');
        }
        $invitationValide = !empty($donneur['invitation_hash']) && !empty($donneur['invitation_expire']) && strtotime((string)$donneur['invitation_expire']) > time();
        if (!(int)$donneur['actif'] && !$invitationValide) {
            throw new RuntimeException('Ce compte donneur est désactivé.');
        }

        $q = connexion()->prepare("SELECT COUNT(*) FROM participations WHERE collecte_id=? AND donneur_id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
        $q->execute([$collecteId, $donneurId]);
        if ($q->fetchColumn()) {
            throw new RuntimeException('Ce donneur est déjà inscrit à cette collecte. Utilisez sa réservation pour marquer son arrivée.');
        }
        $this->verifierDelaiDon($donneurId, (string)$collecte['debut']);

        $participation = new ParticipationCollecte(
            null,
            $collecteId,
            null,
            $donneurId,
            'SUR_PLACE',
            'ARRIVEE',
            strtoupper(bin2hex(random_bytes(6))),
            null,
            $arriveeLe
        );

        $q = connexion()->prepare('INSERT INTO participations (collecte_id,donneur_id,mode,statut,arrivee_le,code_confirmation) VALUES (?,?,?,?,?,?)');
        $q->execute([
            $participation->getCollecteId(),
            $participation->getDonneurId(),
            $participation->getMode(),
            $participation->getStatut(),
            $participation->getArriveeLe(),
            $participation->getCodeConfirmation(),
        ]);
    }

    public function arriver(int $id, ?string $dateHeure = null): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserParticipation($id, $user);
        $arriveeLe = $this->normaliserDateHeure($dateHeure);
        $q = connexion()->prepare("UPDATE participations SET statut='ARRIVEE',arrivee_le=? WHERE id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
        $q->execute([$arriveeLe, $id]);
    }

    public function retirerParticipation(int $id): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserParticipation($id, $user);
        $q = connexion()->prepare("UPDATE participations p LEFT JOIN dons_sang d ON d.participation_id=p.id SET p.statut=IF(p.statut='ARRIVEE','ABSENTE','ANNULEE') WHERE p.id=? AND p.statut IN ('CONFIRMEE','ARRIVEE') AND d.id IS NULL");
        $q->execute([$id]);
        if (!$q->rowCount()) throw new RuntimeException('Cette participation a déjà été retirée ou son don est confirmé.');
    }

    public function confirmerDon(int $id, int $volumeMl, ?string $dateHeure = null): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserParticipation($id, $user);
        if ($volumeMl <= 0) {
            $volumeMl = DonSang::VOLUME_POCHE_DEFAUT;
        }
        if ($volumeMl < 420 || $volumeMl > 1800) {
            throw new RuntimeException('Sélectionnez un nombre de poches valide (1 poche standard = 450 ml par défaut, jusqu’à 4 poches / 1 800 ml).');
        }

        $qCheck = connexion()->prepare("SELECT p.id, p.arrivee_le FROM participations p JOIN examens_pre_don x ON x.participation_id=p.id WHERE p.id=? AND p.statut='ARRIVEE' AND x.decision='APTE'");
        $qCheck->execute([$id]);
        $row = $qCheck->fetch();
        if (!$row) {
            throw new RuntimeException('Le donneur doit être arrivé et déclaré apte avant la confirmation du don.');
        }

        $effectueLe = ($dateHeure !== null && trim($dateHeure) !== '')
            ? $this->normaliserDateHeure($dateHeure)
            : ($row['arrivee_le'] ? (string)$row['arrivee_le'] : date('Y-m-d H:i:s'));

        $don = new DonSang(
            null,
            $id,
            $effectueLe,
            (int)$user['id'],
            $volumeMl,
            'BL-' . strtoupper(bin2hex(random_bytes(6)))
        );

        $q = connexion()->prepare("
            INSERT INTO dons_sang (participation_id,effectue_le,confirme_par,volume_ml,numero_poche)
            VALUES (?,?,?,?,?)
            ON DUPLICATE KEY UPDATE effectue_le=VALUES(effectue_le), confirme_par=VALUES(confirme_par), volume_ml=VALUES(volume_ml)
        ");
        $q->execute([
            $don->getParticipationId(),
            $don->getEffectueLe(),
            $don->getConfirmePar(),
            $don->getVolumeMl(),
            $don->getNumeroPoche(),
        ]);
    }

    private function autoriserParticipation(int $id, array $user): void
    {
        $q = connexion()->prepare('SELECT collecte_id FROM participations WHERE id=?');
        $q->execute([$id]);
        $collecteId = $q->fetchColumn();
        if (!$collecteId) throw new RuntimeException('Participation introuvable.');
        $this->autoriserCollecte((int)$collecteId, $user);
    }

    private function verifierDelaiDon(int $donneurId, string $dateCollecte): void
    {
        $q = connexion()->prepare('SELECT MAX(d.effectue_le) FROM dons_sang d JOIN participations p ON p.id=d.participation_id WHERE p.donneur_id=?');
        $q->execute([$donneurId]);
        $dernier = $q->fetchColumn();
        if ($dernier) {
            $possible = (new DateTimeImmutable($dernier))->modify('+12 weeks');
            if (new DateTimeImmutable($dateCollecte) < $possible) {
                throw new RuntimeException('Dernier don confirmé le ' . date('d/m/Y', strtotime($dernier)) . '. Une nouvelle collecte est possible à partir du ' . $possible->format('d/m/Y') . ' après nouvel avis du centre.');
            }
        }
        $q = connexion()->prepare("SELECT l.statut_prochain_don,l.prochain_don_le FROM resultats_laboratoire l JOIN participations p ON p.id=l.participation_id JOIN dons_sang d ON d.participation_id=p.id WHERE p.donneur_id=? ORDER BY d.effectue_le DESC LIMIT 1");
        $q->execute([$donneurId]);
        $avis = $q->fetch();
        if ($avis && $avis['statut_prochain_don'] === 'NON_AUTORISE') {
            throw new RuntimeException('Le dernier avis du centre ne permet pas une nouvelle inscription. Contactez le centre.');
        }
        if ($avis && $avis['statut_prochain_don'] === 'AUTORISE' && $avis['prochain_don_le'] && substr($dateCollecte, 0, 10) < $avis['prochain_don_le']) {
            throw new RuntimeException('Le centre a indiqué une prochaine date possible : ' . date('d/m/Y', strtotime($avis['prochain_don_le'])) . '.');
        }
    }

    public function collecteIdPourCreneau(int $creneauId): int
    {
        $q = connexion()->prepare('SELECT collecte_id FROM creneaux WHERE id=?');
        $q->execute([$creneauId]);
        return (int)($q->fetchColumn() ?: 0);
    }

    public function capaciteDefautOrganisateur(int $organisateurId): int
    {
        $q = connexion()->prepare('SELECT capacite_defaut FROM parametres_responsable WHERE responsable_id=?');
        $q->execute([$organisateurId]);
        return (int)($q->fetchColumn() ?: 2);
    }
}
