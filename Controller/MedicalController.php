<?php
declare(strict_types=1);

final class MedicalController
{
    private function autoriser(int $participationId): array
    {
        $staff=exigerRole(['RESPONSABLE','ADMIN']);
        $q=connexion()->prepare('SELECT p.id,p.collecte_id,p.statut,p.donneur_id,c.organisateur_id FROM participations p JOIN collectes c ON c.id=p.collecte_id WHERE p.id=?');
        $q->execute([$participationId]);
        $p=$q->fetch();
        if (!$p || ($staff['role']!=='ADMIN' && (int)$p['organisateur_id']!==(int)$staff['id'])) throw new RuntimeException('Accès refusé à cette participation.');
        return [$staff,$p];
    }
    public function examen(int $id,array $post): void
    {
        [$staff,$p]=$this->autoriser($id);
        verifierCsrf();
        if ($p['statut']!=='ARRIVEE') throw new RuntimeException('Le donneur doit être arrivé.');
        $s=$this->nombre($post,'tension_systolique',70,250);
        $d=$this->nombre($post,'tension_diastolique',40,150);
        $pulse=$this->nombre($post,'pouls',30,200);
        $temp=$this->nombre($post,'temperature_c',34,42);
        $hb=$this->nombre($post,'hemoglobine_g_dl',5,25);
        $weight=$this->nombre($post,'poids_kg',25,250);
        $decision=(string)($post['decision']??'');
        if (!in_array($decision,['EN_ATTENTE','APTE','AJOURNE'],true)) throw new RuntimeException('Décision invalide.');
        if ($decision!=='EN_ATTENTE' && in_array(null,[$s,$d,$pulse,$temp,$hb,$weight],true)) throw new RuntimeException('Remplissez les six mesures avant de choisir Apte ou Ajourné.');
        $q=connexion()->prepare('INSERT INTO examens_pre_don (participation_id,tension_systolique,tension_diastolique,pouls,temperature_c,hemoglobine_g_dl,poids_kg,decision,saisi_par) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE tension_systolique=VALUES(tension_systolique),tension_diastolique=VALUES(tension_diastolique),pouls=VALUES(pouls),temperature_c=VALUES(temperature_c),hemoglobine_g_dl=VALUES(hemoglobine_g_dl),poids_kg=VALUES(poids_kg),decision=VALUES(decision),saisi_par=VALUES(saisi_par),saisi_le=NOW()');
        $q->execute([$id,$s,$d,$pulse,$temp,$hb,$weight,$decision,$staff['id']]);
    }
    private function nombre(array $post,string $key,float $min,float $max): ?float
    {
        $value=trim((string)($post[$key]??''));
        if ($value==='') return null;
        if (!is_numeric($value) || (float)$value<$min || (float)$value>$max) {
            $labels=['tension_systolique'=>'tension systolique','tension_diastolique'=>'tension diastolique','pouls'=>'pouls','temperature_c'=>'température','hemoglobine_g_dl'=>'hémoglobine','poids_kg'=>'poids'];
            throw new RuntimeException('Vérifiez la '.$labels[$key].' : valeur attendue entre '.$min.' et '.$max.'.');
        }
        return (float)$value;
    }
    public function resultat(int $id,array $post): void
    {
        [$staff,$p]=$this->autoriser($id);
        verifierCsrf();
        $don=connexion()->prepare('SELECT id FROM dons_sang WHERE participation_id=?');
        $don->execute([$id]);
        if (!$don->fetchColumn()) throw new RuntimeException('Le résultat du laboratoire nécessite un don confirmé.');
        $group=(string)($post['groupe_confirme']??'');
        $status=(string)($post['statut']??'');
        $suivi=(string)($post['statut_prochain_don']??'EN_COURS');
        $date=trim((string)($post['prochain_don_le']??''));
        $tests=[];
        foreach(['vih','vhb','vhc','syphilis'] as $key) {
            $value=(string)($post[$key]??'EN_ATTENTE');
            if (!in_array($value,['EN_ATTENTE','NON_REACTIF','A_CONFIRMER'],true)) throw new RuntimeException('Résultat de dépistage invalide.');
            $tests[]=$value;
        }
        if ($group!=='' && !in_array($group,SosController::GROUPES,true)) throw new RuntimeException('Groupe sanguin invalide.');
        if (!in_array($status,['EN_ATTENTE','VALIDE','CONTACT_CENTRE'],true)) throw new RuntimeException('Statut invalide.');
        if (!in_array($suivi,['EN_COURS','AUTORISE','NON_AUTORISE'],true)) throw new RuntimeException('Statut du prochain don invalide.');
        $dateObj=$date!=='' ? DateTimeImmutable::createFromFormat('!Y-m-d',$date) : false;
        if ($date!=='' && (!$dateObj || $dateObj->format('Y-m-d')!==$date)) throw new RuntimeException('Date du prochain don invalide.');
        if ($suivi==='AUTORISE' && ($status!=='VALIDE' || $date==='')) throw new RuntimeException('Un avis favorable exige des résultats validés et une date indiquée par le centre.');
        if ($status==='VALIDE' && ($group==='' || count(array_filter($tests,fn($v)=>$v==='NON_REACTIF'))!==4)) throw new RuntimeException('La validation publique exige un groupe confirmé et quatre dépistages non réactifs. Si un contrôle est nécessaire, choisissez « Contacter le centre ».');
        $q=connexion()->prepare('INSERT INTO resultats_laboratoire (participation_id,groupe_confirme,statut,vih,vhb,vhc,syphilis,statut_prochain_don,prochain_don_le,saisi_par) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE groupe_confirme=VALUES(groupe_confirme),statut=VALUES(statut),vih=VALUES(vih),vhb=VALUES(vhb),vhc=VALUES(vhc),syphilis=VALUES(syphilis),statut_prochain_don=VALUES(statut_prochain_don),prochain_don_le=VALUES(prochain_don_le),saisi_par=VALUES(saisi_par),saisi_le=NOW()');
        $q->execute([$id,$group?:null,$status,...$tests,$suivi,$suivi==='AUTORISE'?$date:null,$staff['id']]);
    }
    public function pourDonneur(int $donneurId): array
    {
        $q=connexion()->prepare('SELECT p.id,p.arrivee_le,d.effectue_le,c.titre,c.debut,x.tension_systolique,x.tension_diastolique,x.pouls,x.temperature_c,x.hemoglobine_g_dl,x.poids_kg,x.decision,x.saisi_le AS examen_saisi_le,l.groupe_confirme,l.statut AS resultat_statut,l.statut_prochain_don,l.prochain_don_le,l.vih,l.vhb,l.vhc,l.syphilis FROM participations p JOIN collectes c ON c.id=p.collecte_id LEFT JOIN dons_sang d ON d.participation_id=p.id LEFT JOIN examens_pre_don x ON x.participation_id=p.id LEFT JOIN resultats_laboratoire l ON l.participation_id=p.id WHERE p.donneur_id=? ORDER BY c.debut DESC');
        $q->execute([$donneurId]); return $q->fetchAll();
    }
    public function pourCollecte(int $collecteId): array
    {
        $q=connexion()->prepare('SELECT p.id,x.decision,x.tension_systolique,x.tension_diastolique,x.pouls,x.temperature_c,x.hemoglobine_g_dl,x.poids_kg,x.saisi_le AS examen_saisi_le,l.statut AS resultat_statut,l.statut_prochain_don,l.prochain_don_le,l.groupe_confirme,l.vih,l.vhb,l.vhc,l.syphilis,l.saisi_le AS resultat_saisi_le FROM participations p LEFT JOIN examens_pre_don x ON x.participation_id=p.id LEFT JOIN resultats_laboratoire l ON l.participation_id=p.id WHERE p.collecte_id=?');
        $q->execute([$collecteId]);
        $map=[]; foreach($q->fetchAll() as $x) $map[$x['id']]=$x;
        return $map;
    }
}
