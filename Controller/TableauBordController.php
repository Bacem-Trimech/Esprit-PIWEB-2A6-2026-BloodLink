<?php
declare(strict_types=1);

final class TableauBordController
{
    public function chiffres(): array
    {
        exigerRole(['ADMIN']);
        $db = connexion();

        // Synchronise tout examen déclaré APTE qui n'aurait pas encore sa ligne de don confirmé (450 ml par défaut)
        $db->exec("
            INSERT INTO dons_sang (participation_id, effectue_le, confirme_par, volume_ml, numero_poche)
            SELECT p.id, COALESCE(p.arrivee_le, NOW()), x.saisi_par, 450, CONCAT('BL-', UPPER(SUBSTRING(MD5(CONCAT(p.id, '-', UNIX_TIMESTAMP())), 1, 12)))
            FROM participations p
            JOIN examens_pre_don x ON x.participation_id = p.id
            LEFT JOIN dons_sang d ON d.participation_id = p.id
            WHERE p.statut = 'ARRIVEE' AND x.decision = 'APTE' AND d.id IS NULL
        ");

        $donneursActifs = (int)$db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='DONNEUR' AND actif=1")->fetchColumn();
        $responsablesActifs = (int)$db->query("SELECT COUNT(*) FROM utilisateurs WHERE role LIKE 'RESPONSABLE%' AND actif=1")->fetchColumn();
        $collectesTotal = (int)$db->query('SELECT COUNT(*) FROM collectes')->fetchColumn();
        $collectesPubliees = (int)$db->query("SELECT COUNT(*) FROM collectes WHERE statut='PUBLIEE'")->fetchColumn();
        $collectesCloturees = (int)$db->query("SELECT COUNT(*) FROM collectes WHERE statut='CLOTUREE'")->fetchColumn();
        $reservations = (int)$db->query("SELECT COUNT(*) FROM participations WHERE mode='EN_LIGNE' AND statut NOT IN ('ANNULEE','ABSENTE')")->fetchColumn();
        $visitesSurPlace = (int)$db->query("SELECT COUNT(*) FROM participations WHERE mode='SUR_PLACE' AND statut NOT IN ('ANNULEE','ABSENTE')")->fetchColumn();
        $arrivees = (int)$db->query("SELECT COUNT(*) FROM participations WHERE statut='ARRIVEE'")->fetchColumn();
        $examensAptes = (int)$db->query("SELECT COUNT(*) FROM examens_pre_don WHERE decision='APTE'")->fetchColumn();
        $examensAjournes = (int)$db->query("SELECT COUNT(*) FROM examens_pre_don WHERE decision='AJOURNE'")->fetchColumn();
        $donsConfirmes = (int)$db->query('SELECT COUNT(*) FROM dons_sang')->fetchColumn();
        $volumeTotalMl = (int)$db->query('SELECT COALESCE(SUM(volume_ml),0) FROM dons_sang')->fetchColumn();

        // Sang sain et confirmé par le labo : statut VALIDE + groupe_confirme renseigné + 4 dépistages NON_REACTIF (aucune infection)
        $condSain = "l.statut='VALIDE' AND l.groupe_confirme IS NOT NULL AND l.vih='NON_REACTIF' AND l.vhb='NON_REACTIF' AND l.vhc='NON_REACTIF' AND l.syphilis='NON_REACTIF'";
        $condEcarte = "l.statut='CONTACT_CENTRE' OR l.vih='A_CONFIRMER' OR l.vhb='A_CONFIRMER' OR l.vhc='A_CONFIRMER' OR l.syphilis='A_CONFIRMER'";

        $volumeValideMl = (int)$db->query("SELECT COALESCE(SUM(d.volume_ml),0) FROM dons_sang d JOIN resultats_laboratoire l ON l.participation_id=d.participation_id WHERE {$condSain}")->fetchColumn();
        $pochesValidees = (int)$db->query("SELECT COALESCE(SUM(GREATEST(1, ROUND(d.volume_ml / 450))),0) FROM dons_sang d JOIN resultats_laboratoire l ON l.participation_id=d.participation_id WHERE {$condSain}")->fetchColumn();
        $volumeEcarteMl = (int)$db->query("SELECT COALESCE(SUM(d.volume_ml),0) FROM dons_sang d JOIN resultats_laboratoire l ON l.participation_id=d.participation_id WHERE {$condEcarte}")->fetchColumn();
        $volumeInconnuMl = max(0, $volumeTotalMl - $volumeValideMl - $volumeEcarteMl);
        $pochesInconnues = (int)$db->query("SELECT COALESCE(SUM(GREATEST(1, ROUND(d.volume_ml / 450))),0) FROM dons_sang d LEFT JOIN resultats_laboratoire l ON l.participation_id=d.participation_id WHERE l.participation_id IS NULL OR NOT ({$condSain}) AND NOT ({$condEcarte})")->fetchColumn();

        $sosActives = (int)$db->query('SELECT COUNT(*) FROM alertes_sos WHERE expire_le>NOW() AND supprime_le IS NULL')->fetchColumn();
        $pagesVues = (int)$db->query('SELECT COALESCE(SUM(vues),0) FROM statistiques_jour')->fetchColumn();

        // Répartition des donneurs selon le délai de 12 semaines et l'avis du centre
        $donneursRows = $db->query("
            SELECT u.id, u.nom, u.email, u.groupe_sanguin,
                   MAX(d.effectue_le) AS dernier_don_le,
                   COUNT(d.id) AS nb_dons,
                   COALESCE(SUM(d.volume_ml), 0) AS volume_donne_ml,
                   (SELECT l.statut_prochain_don FROM resultats_laboratoire l JOIN participations p2 ON p2.id=l.participation_id JOIN dons_sang d2 ON d2.participation_id=p2.id WHERE p2.donneur_id=u.id ORDER BY d2.effectue_le DESC LIMIT 1) AS statut_prochain_don,
                   (SELECT l.prochain_don_le FROM resultats_laboratoire l JOIN participations p2 ON p2.id=l.participation_id JOIN dons_sang d2 ON d2.participation_id=p2.id WHERE p2.donneur_id=u.id ORDER BY d2.effectue_le DESC LIMIT 1) AS prochain_don_le,
                   (SELECT l.groupe_confirme FROM resultats_laboratoire l JOIN participations p2 ON p2.id=l.participation_id WHERE p2.donneur_id=u.id AND l.statut='VALIDE' AND l.groupe_confirme IS NOT NULL ORDER BY l.saisi_le DESC LIMIT 1) AS groupe_confirme
            FROM utilisateurs u
            LEFT JOIN participations p ON p.donneur_id = u.id
            LEFT JOIN dons_sang d ON d.participation_id = p.id
            WHERE u.role = 'DONNEUR' AND u.actif = 1
            GROUP BY u.id
            ORDER BY dernier_don_le DESC, u.id DESC
        ")->fetchAll();

        $donneursEligibles = 0;
        $donneursEnDelai = 0;
        $donneursNonAutorises = 0;
        $donneursSuivi = [];
        $now = new DateTimeImmutable('now');

        foreach ($donneursRows as $dr) {
            $statutProchain = (string)($dr['statut_prochain_don'] ?? 'EN_COURS');
            $dernierDon = $dr['dernier_don_le'] ? new DateTimeImmutable((string)$dr['dernier_don_le']) : null;
            $finDelai12s = $dernierDon ? $dernierDon->modify('+12 weeks') : null;
            $dateCentre = !empty($dr['prochain_don_le']) ? new DateTimeImmutable((string)$dr['prochain_don_le'] . ' 00:00:00') : null;
            $prochaineDate = $finDelai12s;
            if ($dateCentre && (!$prochaineDate || $dateCentre > $prochaineDate)) {
                $prochaineDate = $dateCentre;
            }

            if ($statutProchain === 'NON_AUTORISE') {
                $donneursNonAutorises++;
                $etat = 'NON_AUTORISE';
            } elseif ($prochaineDate && $prochaineDate > $now) {
                $donneursEnDelai++;
                $etat = 'DELAI_12_SEMAINES';
            } else {
                $donneursEligibles++;
                $etat = 'ELIGIBLE';
            }

            $joursRestants = ($prochaineDate && $prochaineDate > $now)
                ? (int)ceil(($prochaineDate->getTimestamp() - $now->getTimestamp()) / 86400)
                : 0;

            $donneursSuivi[(int)$dr['id']] = [
                'id' => (int)$dr['id'],
                'nom' => (string)$dr['nom'],
                'email' => (string)$dr['email'],
                'groupe' => (string)($dr['groupe_confirme'] ?: 'Non confirmé'),
                'nb_dons' => (int)$dr['nb_dons'],
                'volume_ml' => (int)$dr['volume_donne_ml'],
                'dernier_don_le' => $dr['dernier_don_le'],
                'prochain_don_possible_le' => $prochaineDate ? $prochaineDate->format('Y-m-d H:i:s') : null,
                'jours_restants' => $joursRestants,
                'etat' => $etat,
            ];
        }

        // Répartition du sang collecté par groupe sanguin : UNIQUEMENT les poches confirmées saines par le labo ont un groupe validé ; les autres sont "En attente / Non confirmé"
        $groupesBase = [];
        foreach (SosController::GROUPES as $g) {
            $groupesBase[$g] = [
                'groupe' => $g,
                'poches' => 0,
                'volume_ml' => 0,
                'sain' => true,
            ];
        }
        $groupesBase['En attente labo'] = [
            'groupe' => 'En attente labo',
            'poches' => 0,
            'volume_ml' => 0,
            'sain' => false,
        ];

        $donsAllRows = $db->query("
            SELECT d.id, d.volume_ml, d.effectue_le, d.numero_poche,
                   p.collecte_id, p.donneur_id,
                   u.nom AS donneur_nom, u.email AS donneur_email,
                   l.groupe_confirme, l.statut AS labo_statut,
                   l.vih, l.vhb, l.vhc, l.syphilis,
                   l.statut_prochain_don, l.prochain_don_le
            FROM dons_sang d
            JOIN participations p ON p.id = d.participation_id
            JOIN utilisateurs u ON u.id = p.donneur_id
            LEFT JOIN resultats_laboratoire l ON l.participation_id = p.id
            ORDER BY d.effectue_le DESC
        ")->fetchAll();

        $groupesParCollecte = [];
        $donsParCollecte = [];

        foreach ($donsAllRows as $row) {
            $cid = (int)$row['collecte_id'];
            $vol = (int)$row['volume_ml'];
            $estSainConfirme = ($row['labo_statut'] === 'VALIDE')
                && !empty($row['groupe_confirme'])
                && $row['vih'] === 'NON_REACTIF'
                && $row['vhb'] === 'NON_REACTIF'
                && $row['vhc'] === 'NON_REACTIF'
                && $row['syphilis'] === 'NON_REACTIF';
            $estEcarte = ($row['labo_statut'] === 'CONTACT_CENTRE')
                || in_array('A_CONFIRMER', [$row['vih'] ?? '', $row['vhb'] ?? '', $row['vhc'] ?? '', $row['syphilis'] ?? ''], true);

            if ($estSainConfirme) {
                $gKey = (string)$row['groupe_confirme'];
                $etatPoche = 'SAIN_CONFIRME';
                $libelleGroupe = $gKey . ' (Sain)';
            } elseif ($estEcarte) {
                $gKey = 'Écarté (contrôle)';
                $etatPoche = 'ECARTE';
                $libelleGroupe = 'Écarté (contrôle)';
            } else {
                $gKey = 'En attente labo';
                $etatPoche = 'EN_ATTENTE';
                $libelleGroupe = 'En attente / Inconnu';
            }

            $nbPochesRow = DonSang::compterPoches($vol);
            if (isset($groupesBase[$gKey])) {
                $groupesBase[$gKey]['poches'] += $nbPochesRow;
                $groupesBase[$gKey]['volume_ml'] += $vol;
            }

            if (!isset($groupesParCollecte[$cid][$gKey])) {
                $groupesParCollecte[$cid][$gKey] = [
                    'groupe' => $gKey,
                    'etat' => $etatPoche,
                    'poches' => 0,
                    'volume_ml' => 0,
                ];
            }
            $groupesParCollecte[$cid][$gKey]['poches'] += $nbPochesRow;
            $groupesParCollecte[$cid][$gKey]['volume_ml'] += $vol;

            $suiviDonneur = $donneursSuivi[(int)$row['donneur_id']] ?? null;
            $donsParCollecte[$cid][] = [
                'numero_poche' => (string)$row['numero_poche'],
                'effectue_le' => (string)$row['effectue_le'],
                'volume_ml' => $vol,
                'donneur_id' => (int)$row['donneur_id'],
                'donneur_nom' => (string)$row['donneur_nom'],
                'donneur_email' => (string)$row['donneur_email'],
                'groupe_affiche' => $libelleGroupe,
                'etat_poche' => $etatPoche,
                'delai_etat' => $suiviDonneur['etat'] ?? 'ELIGIBLE',
                'jours_restants' => $suiviDonneur['jours_restants'] ?? 0,
                'prochain_don_possible_le' => $suiviDonneur['prochain_don_possible_le'] ?? null,
            ];
        }

        // Participations par collecte (inclut aussi les inscrits/arrivés qui n'ont pas encore de don confirmé)
        $partRows = $db->query("
            SELECT p.id, p.collecte_id, p.donneur_id, p.mode, p.statut, p.arrivee_le, p.code_confirmation,
                   u.nom, u.email,
                   COALESCE(x.decision, 'EN_ATTENTE') AS examen_decision,
                   d.id AS don_id, d.volume_ml, d.numero_poche, d.effectue_le,
                   l.groupe_confirme, l.statut AS labo_statut, l.vih, l.vhb, l.vhc, l.syphilis
            FROM participations p
            JOIN utilisateurs u ON u.id = p.donneur_id
            LEFT JOIN examens_pre_don x ON x.participation_id = p.id
            LEFT JOIN dons_sang d ON d.participation_id = p.id
            LEFT JOIN resultats_laboratoire l ON l.participation_id = p.id
            WHERE p.statut NOT IN ('ANNULEE','ABSENTE')
            ORDER BY d.effectue_le DESC, p.cree_le DESC
        ")->fetchAll();

        $participantsParCollecte = [];
        foreach ($partRows as $pr) {
            $cid = (int)$pr['collecte_id'];
            $did = (int)$pr['donneur_id'];
            $suivi = $donneursSuivi[$did] ?? null;
            $estSain = ($pr['labo_statut'] === 'VALIDE')
                && !empty($pr['groupe_confirme'])
                && $pr['vih'] === 'NON_REACTIF'
                && $pr['vhb'] === 'NON_REACTIF'
                && $pr['vhc'] === 'NON_REACTIF'
                && $pr['syphilis'] === 'NON_REACTIF';
            $participantsParCollecte[$cid][] = [
                'donneur_id' => $did,
                'nom' => (string)$pr['nom'],
                'email' => (string)$pr['email'],
                'mode' => (string)$pr['mode'],
                'statut' => (string)$pr['statut'],
                'examen_decision' => (string)$pr['examen_decision'],
                'a_donne' => !empty($pr['don_id']),
                'volume_ml' => (int)($pr['volume_ml'] ?? 0),
                'numero_poche' => (string)($pr['numero_poche'] ?? ''),
                'effectue_le' => $pr['effectue_le'],
                'est_sain' => $estSain,
                'groupe_confirme' => $estSain ? (string)$pr['groupe_confirme'] : 'En attente / Inconnu',
                'delai_etat' => $suivi['etat'] ?? 'ELIGIBLE',
                'jours_restants' => (int)($suivi['jours_restants'] ?? 0),
                'prochain_don_possible_le' => $suivi['prochain_don_possible_le'] ?? null,
            ];
        }

        // Bilan détaillé par collecte
        $collectesRows = $db->query("
            SELECT c.id, c.titre, c.ville, c.adresse, c.debut, c.fin, c.statut,
                   u.nom AS organisateur, COALESCE(r.nom_organisation, 'BloodLink') AS nom_organisation,
                   (SELECT COUNT(*) FROM creneaux cr WHERE cr.collecte_id = c.id) AS nb_creneaux,
                   (SELECT COALESCE(SUM(cr.capacite), 0) FROM creneaux cr WHERE cr.collecte_id = c.id) AS capacite_totale,
                   COUNT(DISTINCT CASE WHEN p.mode = 'EN_LIGNE' AND p.statut NOT IN ('ANNULEE','ABSENTE') THEN p.id END) AS nb_reservations,
                   COUNT(DISTINCT CASE WHEN p.mode = 'SUR_PLACE' AND p.statut NOT IN ('ANNULEE','ABSENTE') THEN p.id END) AS nb_sur_place,
                   COUNT(DISTINCT CASE WHEN p.statut = 'ARRIVEE' THEN p.id END) AS nb_arrivees,
                   COUNT(DISTINCT CASE WHEN x.decision = 'APTE' THEN p.id END) AS nb_aptes,
                   COUNT(DISTINCT CASE WHEN x.decision = 'AJOURNE' THEN p.id END) AS nb_ajournes,
                   COUNT(DISTINCT d.id) AS nb_dons,
                   COALESCE(SUM(d.volume_ml), 0) AS volume_ml,
                   COALESCE(SUM(CASE WHEN {$condSain} THEN d.volume_ml ELSE 0 END), 0) AS volume_valide_ml
            FROM collectes c
            JOIN utilisateurs u ON u.id = c.organisateur_id
            LEFT JOIN parametres_responsable r ON r.responsable_id = u.id
            LEFT JOIN participations p ON p.collecte_id = c.id
            LEFT JOIN examens_pre_don x ON x.participation_id = p.id
            LEFT JOIN dons_sang d ON d.participation_id = p.id
            LEFT JOIN resultats_laboratoire l ON l.participation_id = p.id
            GROUP BY c.id
            ORDER BY c.debut DESC
        ")->fetchAll();

        foreach ($collectesRows as &$cr) {
            $cid = (int)$cr['id'];
            $cr['volume_inconnu_ml'] = max(0, (int)$cr['volume_ml'] - (int)$cr['volume_valide_ml']);
            $cr['groupes'] = array_values($groupesParCollecte[$cid] ?? []);
            $cr['dons_details'] = $donsParCollecte[$cid] ?? [];
            $cr['participants_details'] = $participantsParCollecte[$cid] ?? [];
        }
        unset($cr);

        return [
            'donneurs' => $donneursActifs,
            'donneurs_eligibles' => $donneursEligibles,
            'donneurs_delai_12s' => $donneursEnDelai,
            'donneurs_non_autorises' => $donneursNonAutorises,
            'responsables' => $responsablesActifs,
            'collectes' => $collectesTotal,
            'collectes_publiees' => $collectesPubliees,
            'collectes_cloturees' => $collectesCloturees,
            'reservations' => $reservations,
            'visites_sur_place' => $visitesSurPlace,
            'arrivees' => $arrivees,
            'examens_aptes' => $examensAptes,
            'examens_ajournes' => $examensAjournes,
            'dons_confirmes' => $donsConfirmes,
            'volume_ml' => $volumeTotalMl,
            'volume_valide_ml' => $volumeValideMl,
            'volume_inconnu_ml' => $volumeInconnuMl,
            'poches_validees' => $pochesValidees,
            'poches_inconnues' => $pochesInconnues,
            'sos_actives' => $sosActives,
            'pages_vues' => $pagesVues,
            'par_groupe' => array_values($groupesBase),
            'par_collecte' => $collectesRows,
            'donneurs_suivi' => array_values($donneursSuivi),
            'nb_etablissements' => (int)$db->query('SELECT COUNT(*) FROM etablissements_sante')->fetchColumn(),
            'nb_services' => (int)$db->query('SELECT COUNT(*) FROM services_medicaux')->fetchColumn(),
            'nb_praticiens' => (int)$db->query('SELECT COUNT(*) FROM praticiens')->fetchColumn(),
            'nb_rdv' => (int)$db->query("SELECT COUNT(*) FROM rendez_vous WHERE statut <> 'Annulé'")->fetchColumn(),
            'nb_pharmacies_garde' => (int)$db->query('SELECT COUNT(DISTINCT etablissement_id) FROM gardes_pharmacie WHERE CURDATE() BETWEEN start_date AND end_date')->fetchColumn(),
            'nb_medicaments' => (int)$db->query('SELECT COUNT(*) FROM medicaments')->fetchColumn(),
            'nb_analyses' => (int)$db->query('SELECT COUNT(*) FROM analyse_medicale')->fetchColumn(),
            'nb_comptes_rendus' => (int)$db->query('SELECT COUNT(*) FROM compte_rendu_analyse')->fetchColumn(),
        ];
    }

    public function compterVue(): void
    {
        $q = connexion()->prepare('INSERT INTO statistiques_jour (jour,vues) VALUES (CURRENT_DATE,1) ON DUPLICATE KEY UPDATE vues=vues+1');
        $q->execute();
    }
}
