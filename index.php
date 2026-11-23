<?php
declare(strict_types=1);
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/Service/Securite.php';
foreach (['Utilisateur','CollecteSang','CreneauCollecte','ParticipationCollecte','DonSang','ExamenPreDon','ResultatLaboratoire','AlerteSos','ParametreResponsable'] as $modelClass) {
    require_once __DIR__.'/Model/'.$modelClass.'.php';
}
foreach (['AuthController','UtilisateurController','CollecteController','InvitationController','CompteController','SosController','TableauBordController','ParametreController','MedicalController'] as $class) {
    require_once __DIR__.'/Controller/'.$class.'.php';
}
demarrerSession();
$auth=new AuthController(); $users=new UtilisateurController(); $collectes=new CollecteController();
$invitations=new InvitationController(); $comptes=new CompteController();
$sos=new SosController(); $tableau=new TableauBordController(); $parametres=new ParametreController(); $medical=new MedicalController();
$page=(string)($_GET['page']??'accueil');
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT)?:0;
$errors=[]; $message=''; $items=[]; $detail=null; $slots=[]; $participations=[];
$profile=null; $examens=[]; $medicalMap=[]; $stats=[]; $settings=[]; $scan=null; $current=null; $eligibilite=null;
try {
    $current=utilisateur();
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        try {
            switch($page) {
                case 'inscription':
                    $errors=$auth->inscription($_POST);
                    if (!$errors) { $_SESSION['inscription_reussie']=true; rediriger('connexion'); }
                    http_response_code(422);
                    break;
                case 'connexion':
                    $message=$auth->connexion($_POST);
                    if ($message==='') {
                        $current=utilisateur();
                        if (!$current['profil_complet']) { $_SESSION['bienvenue']=true; rediriger('profil'); }
                        rediriger(match($current['role']) {'ADMIN'=>'tableau-bord','RESPONSABLE'=>'gestion-collectes',default=>'mes-participations'});
                    }
                    http_response_code(422);
                    break;
                case 'deconnexion':
                    $auth->deconnexion();
                case 'profil-sauver':
                    $page='profil';
                    $errors=$comptes->sauverProfil($_POST);
                    if (!$errors) rediriger('profil');
                    http_response_code(422);
                    break;
                case 'mot-de-passe':
                    $page='profil';
                    $errors=$comptes->changerMotDePasse($_POST);
                    if (!$errors) { $_SESSION['mot_de_passe_change']=true; rediriger('profil'); }
                    http_response_code(422);
                    break;
                case 'qr-renouveler':
                    $page='mon-qr';
                    $comptes->qr();
                    rediriger('mon-qr');
                case 'inviter-responsable':
                    $invitations->inviterResponsable($_POST);
                    rediriger('inviter-responsable');
                case 'inviter-donneur':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $invitations->inviterDonneur($_POST);
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'activer':
                    $invitations->activer($_POST);
                    $_SESSION['activation_reussie']=true; rediriger('connexion');
                case 'utilisateur-sauver':
                    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT)?:0;
                    $page='utilisateur-formulaire';
                    $errors=$users->enregistrer($_POST,$id?:null);
                    if (!$errors) rediriger('utilisateurs');
                    http_response_code(422);
                    break;
                case 'utilisateur-supprimer':
                    $page='utilisateurs';
                    $users->supprimer((int)($_POST['id']??0));
                    rediriger('utilisateurs');
                case 'collecte-sauver':
                    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT)?:0;
                    $page='collecte-formulaire';
                    $errors=$collectes->sauverCollecte($_POST,$id?:null);
                    if (!$errors) rediriger('gestion-collectes');
                    http_response_code(422);
                    break;
                case 'creneau-sauver':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $errors=$collectes->sauverCreneau($_POST);
                    if (!$errors) { header('Location: index.php?page=collecte-gestion&id='.$id); exit; }
                    http_response_code(422);
                    break;
                case 'reserver':
                    $creneauId=(int)($_POST['creneau_id']??0);
                    $id=$collectes->collecteIdPourCreneau($creneauId);
                    $page=$id?'collecte':'collectes';
                    $collectes->reserver($creneauId);
                    rediriger('mes-participations');
                case 'annuler':
                    $page='mes-participations';
                    $collectes->annuler((int)($_POST['id']??0));
                    rediriger('mes-participations');
                case 'sur-place':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $collectes->enregistrerSurPlace($id,(string)($_POST['donneur_id']??''),isset($_POST['arrivee_le'])?(string)$_POST['arrivee_le']:null);
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'arriver':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $collectes->arriver((int)($_POST['participation_id']??0),isset($_POST['arrivee_le'])?(string)$_POST['arrivee_le']:null);
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'participation-retirer':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $collectes->retirerParticipation((int)($_POST['participation_id']??0));
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'examen-sauver':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $medical->examen((int)($_POST['participation_id']??0),$_POST);
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'resultat-sauver':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $medical->resultat((int)($_POST['participation_id']??0),$_POST);
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'confirmer-don':
                    $id=(int)($_POST['collecte_id']??0);
                    $page='collecte-gestion';
                    $collectes->confirmerDon((int)($_POST['participation_id']??0),(int)($_POST['volume_ml']??0),isset($_POST['effectue_le'])?(string)$_POST['effectue_le']:null);
                    header('Location: index.php?page=collecte-gestion&id='.$id); exit;
                case 'sos-creer':
                    $page='sos-nouveau';
                    $errors=$sos->creer($_POST);
                    if (!$errors) rediriger('sos');
                    http_response_code(422);
                    break;
                case 'sos-supprimer':
                    $page='gestion-sos';
                    $sos->supprimer((int)($_POST['id']??0));
                    rediriger('gestion-sos');
                case 'parametres-sauver':
                    $page='parametres';
                    $errors=$parametres->sauver($_POST);
                    if (!$errors) rediriger('parametres');
                    http_response_code(422);
                    break;
                default:
                    http_response_code(404); $page='introuvable';
            }
        } catch (Throwable $postError) {
            if ($postError instanceof PDOException) {
                throw $postError;
            }
            if (http_response_code()<400) http_response_code(422);
            $message=$postError->getMessage();
        }
    } elseif ($_SERVER['REQUEST_METHOD']!=='GET') {
        http_response_code(405); throw new RuntimeException('Méthode non autorisée.');
    }
    if ($page==='accueil' && $current) {
        if (!$current['profil_complet']) rediriger('profil');
        if ($current['role']==='ADMIN') rediriger('tableau-bord');
        if ($current['role']==='RESPONSABLE') rediriger('gestion-collectes');
    }
    if ($current && $current['role']==='DONNEUR') {
        $eligibilite=$collectes->etatEligibiliteDonneur((int)$current['id']);
    }
    switch($page) {
        case 'accueil': case 'collectes':
            $items=$collectes->publiques(); $tableau->compterVue(); break;
        case 'collecte':
            $detail=$collectes->detail($id);
            if (!$detail) { http_response_code(404); $page='introuvable'; }
            else $slots=$collectes->creneaux($id);
            $tableau->compterVue(); break;
        case 'mes-participations':
            $current=exigerConnexion();
            $participations=$collectes->mesParticipations((int)$current['id']); break;
        case 'profil': case 'mon-qr':
            $current=exigerConnexion();
            $profile=$comptes->profil();
            if ($page==='mon-qr') $profile['qr_identifiant']=$comptes->assurerQr();
            break;
        case 'mes-examens':
            $current=exigerConnexion();
            $examens=$medical->pourDonneur((int)$current['id']); break;
        case 'scan-donneur':
            exigerRole(['RESPONSABLE']);
            $scan=$comptes->identifierParQr((string)($_GET['code']??''));
            if ($scan) $eligibilite=$collectes->etatEligibiliteDonneur((int)$scan['id']);
            break;
        case 'sos': $items=$sos->liste(); $tableau->compterVue(); break;
        case 'sos-nouveau': $tableau->compterVue(); break;
        case 'tableau-bord': $stats=$tableau->chiffres(); break;
        case 'gestion-sos': exigerRole(['ADMIN']); $items=$sos->liste(true); break;
        case 'parametres': $settings=$parametres->tous(); break;
        case 'inviter-responsable': exigerRole(['ADMIN']); break;
        case 'utilisateurs': $items=$users->liste(); break;
        case 'utilisateur-formulaire':
            exigerRole(['ADMIN']); if ($id) $detail=$users->trouver($id); break;
        case 'gestion-collectes':
            $current=exigerRole(['RESPONSABLE']);
            $items=$collectes->administrables($current); break;
        case 'collecte-formulaire':
            $current=exigerRole(['RESPONSABLE']);
            if ($id) { $collectes->autoriserCollecte($id,$current); $detail=$collectes->detail($id); }
            break;
        case 'collecte-gestion':
            $current=exigerRole(['RESPONSABLE']);
            $collectes->autoriserCollecte($id,$current);
            $detail=$collectes->detail($id);
            $slots=$collectes->creneaux($id);
            $participations=$collectes->participationsCollecte($id,$current);
            $medicalMap=$medical->pourCollecte($id);
            $settings=['capacite_defaut'=>$collectes->capaciteDefautOrganisateur((int)$detail['organisateur_id'])];
            break;
        case 'connexion': case 'inscription': case 'activer': case 'introuvable': break;
        default: http_response_code(404); $page='introuvable';
    }
} catch(Throwable $error) {
    if ($error instanceof PDOException) {
        http_response_code(503);
        $message='Une erreur de base de données est survenue. Vérifiez la configuration et la base de données.';
    } else {
        if (http_response_code()<400) http_response_code(400);
        $message=$error->getMessage();
    }
    if (!in_array($page,['connexion','inscription','activer','profil','utilisateur-formulaire','collecte-formulaire','collecte-gestion','sos-nouveau','parametres','inviter-responsable'],true)) $page='erreur';
}
$staffProfil=($page==='profil' && $current && in_array($current['role'],['ADMIN','RESPONSABLE'],true));
$adminPage=$staffProfil || in_array($page,['tableau-bord','gestion-sos','parametres','inviter-responsable','utilisateurs','utilisateur-formulaire','gestion-collectes','collecte-formulaire','collecte-gestion','scan-donneur'],true);
ob_start();
require __DIR__.($adminPage?'/View/back-office/page.php':'/View/front-office/page.php');
$content=ob_get_clean();
require __DIR__.($adminPage?'/View/back-office/layout.php':'/View/front-office/layout.php');
