<?php
declare(strict_types=1);

final class MedicalController
{
    private function autoriser(int $participationId): array
    {
        $staff = exigerRole(['RESPONSABLE','ADMIN']);
        $q = connexion()->prepare('SELECT p.id,p.collecte_id,p.statut,p.donneur_id,p.arrivee_le,c.organisateur_id FROM participations p JOIN collectes c ON c.id=p.collecte_id WHERE p.id=?');
        $q->execute([$participationId]);
        $p = $q->fetch();
        if (!$p || ($staff['role'] !== 'ADMIN' && (int)$p['organisateur_id'] !== (int)$staff['id'])) {
            throw new RuntimeException('Accès refusé à cette participation.');
        }
        return [$staff, $p];
    }

    public function examen(int $id, array $post): void
    {
        [$staff, $p] = $this->autoriser($id);
        verifierCsrf();
        if ($p['statut'] !== 'ARRIVEE') throw new RuntimeException('Le donneur doit être arrivé.');
        $s = $this->nombre($post, 'tension_systolique', 70, 250);
        $d = $this->nombre($post, 'tension_diastolique', 40, 150);
        if ($s !== null && $d !== null && $d >= $s) {
            throw new RuntimeException('Vérifiez la tension artérielle : la tension diastolique doit être inférieure à la tension systolique.');
        }
        $pulse = $this->nombre($post, 'pouls', 30, 200);
        $temp = $this->nombre($post, 'temperature_c', 34, 42);
        $hb = $this->nombre($post, 'hemoglobine_g_dl', 5, 25);
        $weight = $this->nombre($post, 'poids_kg', 25, 250);
        $decision = (string)($post['decision'] ?? '');
        if (!in_array($decision, ['EN_ATTENTE','APTE','AJOURNE'], true)) throw new RuntimeException('Décision invalide.');
        if ($decision !== 'EN_ATTENTE' && in_array(null, [$s,$d,$pulse,$temp,$hb,$weight], true)) {
            throw new RuntimeException('Remplissez les six mesures avant de choisir Apte ou Ajourné.');
        }

        $don = connexion()->prepare('SELECT id FROM dons_sang WHERE participation_id=?');
        $don->execute([$id]);
        $donExiste = (bool)$don->fetchColumn();
        if ($donExiste && $decision !== 'APTE') {
            throw new RuntimeException('Un don est déjà confirmé pour cette participation : la décision médicale doit rester « Apte ».');
        }

        $saisiLeRaw = str_replace('T', ' ', trim((string)($post['saisi_le'] ?? '')));
        $saisiLe = ($saisiLeRaw !== '' && strtotime($saisiLeRaw))
            ? date('Y-m-d H:i:s', strtotime($saisiLeRaw))
            : date('Y-m-d H:i:s');

        $examen = new ExamenPreDon(
            $id,
            $s !== null ? (int)round($s) : null,
            $d !== null ? (int)round($d) : null,
            $pulse !== null ? (int)round($pulse) : null,
            $temp,
            $hb,
            $weight,
            $decision,
            (int)$staff['id'],
            $saisiLe
        );

        $q = connexion()->prepare('INSERT INTO examens_pre_don (participation_id,tension_systolique,tension_diastolique,pouls,temperature_c,hemoglobine_g_dl,poids_kg,decision,saisi_par,saisi_le) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE tension_systolique=VALUES(tension_systolique),tension_diastolique=VALUES(tension_diastolique),pouls=VALUES(pouls),temperature_c=VALUES(temperature_c),hemoglobine_g_dl=VALUES(hemoglobine_g_dl),poids_kg=VALUES(poids_kg),decision=VALUES(decision),saisi_par=VALUES(saisi_par),saisi_le=VALUES(saisi_le)');
        $q->execute([
            $examen->getParticipationId(),
            $examen->getTensionSystolique(),
            $examen->getTensionDiastolique(),
            $examen->getPouls(),
            $examen->getTemperatureC(),
            $examen->getHemoglobineGDl(),
            $examen->getPoidsKg(),
            $examen->getDecision(),
            $examen->getSaisiPar(),
            $examen->getSaisiLe(),
        ]);

        // Lorsqu'un donneur est vérifié APTE au prélèvement, le don (450 ml par défaut ou volume saisi) est automatiquement comptabilisé dans la collecte
        if ($decision === 'APTE' && !$donExiste) {
            $volInput = isset($post['volume_ml']) && trim((string)$post['volume_ml']) !== '' ? (int)$post['volume_ml'] : 450;
            if ($volInput < 420 || $volInput > 480) {
                $volInput = 450;
            }
            $effectueLe = !empty($p['arrivee_le']) ? (string)$p['arrivee_le'] : $saisiLe;
            $qDon = connexion()->prepare('INSERT IGNORE INTO dons_sang (participation_id,effectue_le,confirme_par,volume_ml,numero_poche) VALUES (?,?,?,?,?)');
            $qDon->execute([
                $id,
                $effectueLe,
                (int)$staff['id'],
                $volInput,
                'BL-' . strtoupper(bin2hex(random_bytes(6))),
            ]);
        }
    }

    private function nombre(array $post, string $key, float $min, float $max): ?float
    {
        $value = str_replace(',', '.', trim((string)($post[$key] ?? '')));
        if ($value === '') return null;
        if (!is_numeric($value) || (float)$value < $min || (float)$value > $max) {
            $labels = [
                'tension_systolique' => 'tension systolique',
                'tension_diastolique' => 'tension diastolique',
                'pouls' => 'pouls',
                'temperature_c' => 'température',
                'hemoglobine_g_dl' => 'hémoglobine',
                'poids_kg' => 'poids',
            ];
            throw new RuntimeException('Vérifiez la ' . $labels[$key] . ' : valeur attendue entre ' . $min . ' et ' . $max . '.');
        }
        return (float)$value;
    }

    public function resultat(int $id, array $post): void
    {
        [$staff, $p] = $this->autoriser($id);
        verifierCsrf();
        $don = connexion()->prepare('SELECT id FROM dons_sang WHERE participation_id=?');
        $don->execute([$id]);
        if (!$don->fetchColumn()) throw new RuntimeException('Le résultat du laboratoire nécessite un don confirmé.');
        $group = (string)($post['groupe_confirme'] ?? '');
        $status = (string)($post['statut'] ?? '');
        $suivi = (string)($post['statut_prochain_don'] ?? 'EN_COURS');
        $date = trim((string)($post['prochain_don_le'] ?? ''));
        $tests = [];
        foreach (['vih','vhb','vhc','syphilis'] as $key) {
            $value = (string)($post[$key] ?? 'EN_ATTENTE');
            if (!in_array($value, ['EN_ATTENTE','NON_REACTIF','A_CONFIRMER'], true)) throw new RuntimeException('Résultat de dépistage invalide.');
            $tests[$key] = $value;
        }
        if ($group !== '' && !in_array($group, SosController::GROUPES, true)) throw new RuntimeException('Groupe sanguin invalide.');
        if (!in_array($status, ['EN_ATTENTE','VALIDE','CONTACT_CENTRE'], true)) throw new RuntimeException('Statut invalide.');
        if (!in_array($suivi, ['EN_COURS','AUTORISE','NON_AUTORISE'], true)) throw new RuntimeException('Statut du prochain don invalide.');
        $dateObj = $date !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        if ($date !== '' && (!$dateObj || $dateObj->format('Y-m-d') !== $date)) throw new RuntimeException('Date du prochain don invalide.');
        if ($suivi === 'AUTORISE' && ($status !== 'VALIDE' || $date === '')) {
            throw new RuntimeException('Un avis favorable exige des résultats validés et une date indiquée par le centre.');
        }
        if ($status === 'VALIDE' && ($group === '' || count(array_filter($tests, static fn(string $v): bool => $v === 'NON_REACTIF')) !== 4)) {
            throw new RuntimeException('La validation publique exige un groupe confirmé et quatre dépistages non réactifs. Si un contrôle est nécessaire, choisissez « Contacter le centre ».');
        }

        $resultat = new ResultatLaboratoire(
            $id,
            $group !== '' ? $group : null,
            $status,
            $tests['vih'],
            $tests['vhb'],
            $tests['vhc'],
            $tests['syphilis'],
            $suivi,
            $suivi === 'AUTORISE' ? $date : null,
            (int)$staff['id']
        );

        $q = connexion()->prepare('INSERT INTO resultats_laboratoire (participation_id,groupe_confirme,statut,vih,vhb,vhc,syphilis,statut_prochain_don,prochain_don_le,saisi_par) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE groupe_confirme=VALUES(groupe_confirme),statut=VALUES(statut),vih=VALUES(vih),vhb=VALUES(vhb),vhc=VALUES(vhc),syphilis=VALUES(syphilis),statut_prochain_don=VALUES(statut_prochain_don),prochain_don_le=VALUES(prochain_don_le),saisi_par=VALUES(saisi_par),saisi_le=NOW()');
        $q->execute([
            $resultat->getParticipationId(),
            $resultat->getGroupeConfirme(),
            $resultat->getStatut(),
            $resultat->getVih(),
            $resultat->getVhb(),
            $resultat->getVhc(),
            $resultat->getSyphilis(),
            $resultat->getStatutProchainDon(),
            $resultat->getProchainDonLe(),
            $resultat->getSaisiPar(),
        ]);

        // Synchronise le groupe sanguin confirmé sur la fiche du donneur
        if ($group !== '') {
            $qUser = connexion()->prepare('UPDATE utilisateurs SET groupe_sanguin=? WHERE id=?');
            $qUser->execute([$group, (int)$p['donneur_id']]);
        }
    }

    public function pourDonneur(int $donneurId): array
    {
        $q = connexion()->prepare('SELECT p.id,p.arrivee_le,d.effectue_le,d.volume_ml,d.numero_poche,c.titre,c.debut,x.tension_systolique,x.tension_diastolique,x.pouls,x.temperature_c,x.hemoglobine_g_dl,x.poids_kg,x.decision,x.saisi_le AS examen_saisi_le,l.groupe_confirme,l.statut AS resultat_statut,l.statut_prochain_don,l.prochain_don_le,l.vih,l.vhb,l.vhc,l.syphilis FROM participations p JOIN collectes c ON c.id=p.collecte_id LEFT JOIN dons_sang d ON d.participation_id=p.id LEFT JOIN examens_pre_don x ON x.participation_id=p.id LEFT JOIN resultats_laboratoire l ON l.participation_id=p.id WHERE p.donneur_id=? ORDER BY c.debut DESC');
        $q->execute([$donneurId]);
        return $q->fetchAll();
    }

    public function pourCollecte(int $collecteId): array
    {
        $q = connexion()->prepare('SELECT p.id,x.decision,x.tension_systolique,x.tension_diastolique,x.pouls,x.temperature_c,x.hemoglobine_g_dl,x.poids_kg,x.saisi_le AS examen_saisi_le,l.statut AS resultat_statut,l.statut_prochain_don,l.prochain_don_le,l.groupe_confirme,l.vih,l.vhb,l.vhc,l.syphilis,l.saisi_le AS resultat_saisi_le FROM participations p LEFT JOIN examens_pre_don x ON x.participation_id=p.id LEFT JOIN resultats_laboratoire l ON l.participation_id=p.id WHERE p.collecte_id=?');
        $q->execute([$collecteId]);
        $map = [];
        foreach ($q->fetchAll() as $x) {
            $map[$x['id']] = $x;
        }
        return $map;
    }
}
