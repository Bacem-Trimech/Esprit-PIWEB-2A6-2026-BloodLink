<?php
declare(strict_types=1);

final class CollecteController
{
    public function publiques(): array
    {
        $q = connexion()->query("SELECT c.*,u.nom AS organisateur,r.nom_organisation FROM collectes c JOIN utilisateurs u ON u.id=c.organisateur_id LEFT JOIN parametres_responsable r ON r.responsable_id=u.id WHERE c.statut='PUBLIEE' AND c.fin>=NOW() ORDER BY c.debut");
        return $q->fetchAll();
    }
    public function detail(int $id): ?array
    {
        $q = connexion()->prepare('SELECT c.*,u.nom AS organisateur,r.nom_organisation,r.contact_public,r.message_accueil FROM collectes c JOIN utilisateurs u ON u.id=c.organisateur_id LEFT JOIN parametres_responsable r ON r.responsable_id=u.id WHERE c.id=?');
        $q->execute([$id]);
        $item = $q->fetch();
        if (!$item || ($item['statut'] !== 'PUBLIEE' && !in_array(utilisateur()['role'] ?? '', ['RESPONSABLE','ADMIN'], true))) return null;
        return $item;
    }
    public function creneaux(int $id): array
    {
        $q = connexion()->prepare("SELECT cr.*,COUNT(CASE WHEN p.statut IN ('CONFIRMEE','ARRIVEE') THEN 1 END) AS places_prises FROM creneaux cr LEFT JOIN participations p ON p.creneau_id=cr.id WHERE cr.collecte_id=? GROUP BY cr.id ORDER BY cr.debut");
        $q->execute([$id]);
        return $q->fetchAll();
    }
    public function mesParticipations(int $id): array
    {
        $q = connexion()->prepare('SELECT p.*,c.titre,c.ville,c.adresse,c.debut AS collecte_debut,cr.debut AS creneau_debut,d.effectue_le,d.volume_ml,d.numero_poche FROM participations p JOIN collectes c ON c.id=p.collecte_id LEFT JOIN creneaux cr ON cr.id=p.creneau_id LEFT JOIN dons_sang d ON d.participation_id=p.id WHERE p.donneur_id=? ORDER BY p.cree_le DESC');
        $q->execute([$id]);
        return $q->fetchAll();
    }
    public function administrables(array $user): array
    {
        $sql = 'SELECT c.*,COUNT(DISTINCT p.id) AS participations,COUNT(DISTINCT d.id) AS dons FROM collectes c LEFT JOIN participations p ON p.collecte_id=c.id LEFT JOIN dons_sang d ON d.participation_id=p.id';
        if ($user['role'] === 'RESPONSABLE') {
            $q = connexion()->prepare($sql . ' WHERE c.organisateur_id=? GROUP BY c.id ORDER BY c.debut DESC');
            $q->execute([$user['id']]);
        } else {
            $q = connexion()->query($sql . ' GROUP BY c.id ORDER BY c.debut DESC');
        }
        return $q->fetchAll();
    }
    public function sauverCollecte(array $post, ?int $id = null): array
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $titre = trim((string)($post['titre'] ?? ''));
        $adresse = trim((string)($post['adresse'] ?? ''));
        $ville = trim((string)($post['ville'] ?? ''));
        $debut = str_replace('T', ' ', (string)($post['debut'] ?? ''));
        $fin = str_replace('T', ' ', (string)($post['fin'] ?? ''));
        $statut = (string)($post['statut'] ?? '');
        $errors = [];
        if (mb_strlen($titre) < 3 || mb_strlen($titre) > 160) $errors[] = 'Titre invalide.';
        if (mb_strlen($adresse) < 5 || mb_strlen($adresse) > 255) $errors[] = 'Adresse invalide.';
        if (mb_strlen($ville) < 2 || mb_strlen($ville) > 100) $errors[] = 'Ville invalide.';
        if (!$debut || !$fin || !strtotime($debut) || !strtotime($fin) || strtotime($fin) <= strtotime($debut)) $errors[] = 'Les dates de la collecte sont invalides.';
        if (!in_array($statut, ['BROUILLON','PUBLIEE','CLOTUREE'], true)) $errors[] = 'Statut invalide.';
        if ($errors) return $errors;
        if ($id) {
            $this->autoriserCollecte($id, $user);
            $q = connexion()->prepare('UPDATE collectes SET titre=?,adresse=?,ville=?,debut=?,fin=?,statut=? WHERE id=?');
            $q->execute([$titre,$adresse,$ville,$debut,$fin,$statut,$id]);
        } else {
            $q = connexion()->prepare('INSERT INTO collectes (titre,adresse,ville,debut,fin,statut,organisateur_id) VALUES (?,?,?,?,?,?,?)');
            $q->execute([$titre,$adresse,$ville,$debut,$fin,$statut,$user['id']]);
        }
        return [];
    }
    public function autoriserCollecte(int $id, array $user): void
    {
        $q = connexion()->prepare('SELECT organisateur_id FROM collectes WHERE id=?');
        $q->execute([$id]);
        $owner = $q->fetchColumn();
        if (!$owner || ($user['role'] !== 'ADMIN' && (int)$owner !== (int)$user['id'])) throw new RuntimeException('Collecte introuvable ou accès refusé.');
    }
    public function sauverCreneau(array $post): array
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $collecteId = filter_var($post['collecte_id'] ?? null,FILTER_VALIDATE_INT);
        if (!$collecteId) return ['Collecte invalide.'];
        $this->autoriserCollecte((int)$collecteId,$user);
        $debut = str_replace('T', ' ', (string)($post['debut'] ?? ''));
        $fin = str_replace('T', ' ', (string)($post['fin'] ?? ''));
        $capacite = filter_var($post['capacite'] ?? null,FILTER_VALIDATE_INT);
        $q = connexion()->prepare('SELECT debut,fin FROM collectes WHERE id=?');
        $q->execute([$collecteId]);
        $collecte = $q->fetch();
        if (!$capacite || $capacite < 1 || $capacite > 100 || !strtotime($debut) || !strtotime($fin) || strtotime($debut) < strtotime($collecte['debut']) || strtotime($fin) > strtotime($collecte['fin']) || strtotime($fin) <= strtotime($debut)) return ['Créneau invalide ou hors des horaires de collecte.'];
        $q = connexion()->prepare("INSERT INTO creneaux (collecte_id,debut,fin,capacite,statut) VALUES (?,?,?,?,'OUVERT')");
        $q->execute([$collecteId,$debut,$fin,$capacite]);
        return [];
    }
    public function reserver(int $creneauId): void
    {
        $user = exigerConnexion();
        verifierCsrf();
        if ($user['role']!=='DONNEUR' || !(int)$user['profil_complet']) throw new RuntimeException('Complétez votre profil donneur avant de réserver.');
        $db = connexion();
        $db->beginTransaction();
        try {
            $q = $db->prepare("SELECT cr.*,c.statut AS collecte_statut,c.fin AS collecte_fin,COALESCE(r.horizon_reservation_jours,60) AS horizon_jours FROM creneaux cr JOIN collectes c ON c.id=cr.collecte_id LEFT JOIN parametres_responsable r ON r.responsable_id=c.organisateur_id WHERE cr.id=? FOR UPDATE");
            $q->execute([$creneauId]);
            $cr = $q->fetch();
            if (!$cr || $cr['statut'] !== 'OUVERT' || $cr['collecte_statut'] !== 'PUBLIEE' || strtotime($cr['debut']) <= time()) throw new RuntimeException('Ce créneau n’est plus disponible.');
            $horizon=(int)$cr['horizon_jours'];
            if ($horizon>0 && strtotime($cr['debut'])>time()+$horizon*86400) throw new RuntimeException('Ce créneau est au-delà de l’horizon de réservation autorisé.');
            $q = $db->prepare("SELECT COUNT(*) FROM participations WHERE creneau_id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
            $q->execute([$creneauId]);
            if ((int)$q->fetchColumn() >= (int)$cr['capacite']) throw new RuntimeException('Ce créneau est complet.');
            $this->verifierDelaiDon((int)$user['id'], (string)$cr['debut']);
            $q = $db->prepare("SELECT COUNT(*) FROM participations WHERE collecte_id=? AND donneur_id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
            $q->execute([$cr['collecte_id'],$user['id']]);
            if ($q->fetchColumn()) throw new RuntimeException('Vous participez déjà à cette collecte.');
            $code = strtoupper(bin2hex(random_bytes(6)));
            $q = $db->prepare("INSERT INTO participations (collecte_id,creneau_id,donneur_id,mode,statut,code_confirmation) VALUES (?,?,?,'EN_LIGNE','CONFIRMEE',?)");
            $q->execute([$cr['collecte_id'],$creneauId,$user['id'],$code]);
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
        $q->execute([$id,$user['id']]);
        if (!$q->rowCount()) throw new RuntimeException('Cette réservation ne peut pas être annulée.');
    }
    public function participationsCollecte(int $collecteId, array $user): array
    {
        $this->autoriserCollecte($collecteId,$user);
        $q = connexion()->prepare("SELECT p.*,u.nom,u.email,cr.debut AS creneau_debut,d.effectue_le FROM participations p JOIN utilisateurs u ON u.id=p.donneur_id LEFT JOIN creneaux cr ON cr.id=p.creneau_id LEFT JOIN dons_sang d ON d.participation_id=p.id WHERE p.collecte_id=? AND p.statut NOT IN ('ANNULEE','ABSENTE') ORDER BY p.cree_le DESC");
        $q->execute([$collecteId]);
        return $q->fetchAll();
    }
    public function enregistrerSurPlace(int $collecteId, string $identifiant): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserCollecte($collecteId,$user);
        $identifiant=trim($identifiant);
        if (preg_match('/^[A-F0-9]{12}$/D',$identifiant)) {
            $q=connexion()->prepare('SELECT id,collecte_id,statut FROM participations WHERE code_confirmation=?');
            $q->execute([$identifiant]); $reservation=$q->fetch();
            if (!$reservation) throw new RuntimeException('Code de réservation introuvable. Vérifiez les 12 caractères.');
            if ((int)$reservation['collecte_id']!==$collecteId) throw new RuntimeException('Cette réservation appartient à une autre collecte.');
            if ($reservation['statut']==='CONFIRMEE') {
                $this->arriver((int)$reservation['id']);
                return;
            }
            throw new RuntimeException('Cette réservation est déjà arrivée, annulée ou terminée.');
        }
        if (ctype_digit($identifiant)) $donneurId=(int)$identifiant;
        elseif (preg_match('/^[a-f0-9]{48}$/D',$identifiant)) {
            $q=connexion()->prepare("SELECT id FROM utilisateurs WHERE qr_identifiant=? AND role='DONNEUR' AND actif=1");
            $q->execute([$identifiant]); $donneurId=(int)$q->fetchColumn();
        } else throw new RuntimeException('Saisissez un identifiant donneur, un code de réservation ou un code QR valide.');
        $q = connexion()->prepare("SELECT id FROM utilisateurs WHERE id=? AND role='DONNEUR'");
        $q->execute([$donneurId]);
        if (!$q->fetch()) throw new RuntimeException('Compte donneur introuvable. Créez d’abord son compte.');
        $q = connexion()->prepare("SELECT COUNT(*) FROM participations WHERE collecte_id=? AND donneur_id=? AND statut IN ('CONFIRMEE','ARRIVEE')");
        $q->execute([$collecteId,$donneurId]);
        if ($q->fetchColumn()) throw new RuntimeException('Ce donneur est déjà inscrit à cette collecte. Utilisez sa réservation pour marquer son arrivée.');
        $q=connexion()->prepare('SELECT debut FROM collectes WHERE id=?');
        $q->execute([$collecteId]);
        $this->verifierDelaiDon($donneurId,(string)$q->fetchColumn());
        $q = connexion()->prepare("INSERT INTO participations (collecte_id,donneur_id,mode,statut,arrivee_le,code_confirmation) VALUES (?,?,'SUR_PLACE','ARRIVEE',NOW(),?)");
        $q->execute([$collecteId,$donneurId,strtoupper(bin2hex(random_bytes(6)))]);
    }
    public function arriver(int $id): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserParticipation($id,$user);
        $q = connexion()->prepare("UPDATE participations SET statut='ARRIVEE',arrivee_le=NOW() WHERE id=? AND statut='CONFIRMEE'");
        $q->execute([$id]);
    }
    public function retirerParticipation(int $id): void
    {
        $user=exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserParticipation($id,$user);
        $q=connexion()->prepare("UPDATE participations p LEFT JOIN dons_sang d ON d.participation_id=p.id SET p.statut=IF(p.statut='ARRIVEE','ABSENTE','ANNULEE') WHERE p.id=? AND p.statut IN ('CONFIRMEE','ARRIVEE') AND d.id IS NULL");
        $q->execute([$id]);
        if (!$q->rowCount()) throw new RuntimeException('Cette participation a déjà été retirée ou son don est confirmé.');
    }
    public function confirmerDon(int $id, int $volumeMl): void
    {
        $user = exigerRole(['RESPONSABLE','ADMIN']);
        verifierCsrf();
        $this->autoriserParticipation($id,$user);
        if ($volumeMl < 420 || $volumeMl > 480) throw new RuntimeException('Indiquez le volume réellement prélevé, entre 420 et 480 ml pour ce don de sang total.');
        $q = connexion()->prepare("INSERT INTO dons_sang (participation_id,effectue_le,confirme_par,volume_ml,numero_poche) SELECT p.id,COALESCE(p.arrivee_le,NOW()),?,?,? FROM participations p JOIN examens_pre_don x ON x.participation_id=p.id WHERE p.id=? AND p.statut='ARRIVEE' AND x.decision='APTE'");
        $q->execute([$user['id'],$volumeMl,'BL-'.strtoupper(bin2hex(random_bytes(6))),$id]);
        if (!$q->rowCount()) throw new RuntimeException('Le donneur doit être arrivé et déclaré apte avant la confirmation du don.');
    }
    private function autoriserParticipation(int $id, array $user): void
    {
        $q = connexion()->prepare('SELECT collecte_id FROM participations WHERE id=?');
        $q->execute([$id]);
        $collecteId = $q->fetchColumn();
        if (!$collecteId) throw new RuntimeException('Participation introuvable.');
        $this->autoriserCollecte((int)$collecteId,$user);
    }
    private function verifierDelaiDon(int $donneurId, string $dateCollecte): void
    {
        $q=connexion()->prepare('SELECT MAX(d.effectue_le) FROM dons_sang d JOIN participations p ON p.id=d.participation_id WHERE p.donneur_id=?');
        $q->execute([$donneurId]);
        $dernier=$q->fetchColumn();
        if ($dernier) {
            $possible=(new DateTimeImmutable($dernier))->modify('+12 weeks');
            if (new DateTimeImmutable($dateCollecte)<$possible) throw new RuntimeException('Dernier don confirmé le '.date('d/m/Y',strtotime($dernier)).'. Une nouvelle collecte est possible à partir du '.$possible->format('d/m/Y').' après nouvel avis du centre.');
        }
        $q=connexion()->prepare("SELECT l.statut_prochain_don,l.prochain_don_le FROM resultats_laboratoire l JOIN participations p ON p.id=l.participation_id JOIN dons_sang d ON d.participation_id=p.id WHERE p.donneur_id=? ORDER BY d.effectue_le DESC LIMIT 1");
        $q->execute([$donneurId]); $avis=$q->fetch();
        if ($avis && $avis['statut_prochain_don']==='NON_AUTORISE') throw new RuntimeException('Le dernier avis du centre ne permet pas une nouvelle inscription. Contactez le centre.');
        if ($avis && $avis['statut_prochain_don']==='AUTORISE' && $avis['prochain_don_le'] && substr($dateCollecte,0,10)<$avis['prochain_don_le']) throw new RuntimeException('Le centre a indiqué une prochaine date possible : '.date('d/m/Y',strtotime($avis['prochain_don_le'])).'.');
    }
}
