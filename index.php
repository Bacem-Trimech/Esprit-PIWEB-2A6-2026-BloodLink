<?php
declare(strict_types=1);
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/Service/Securite.php';
require_once __DIR__.'/Controller/AuthController.php';
require_once __DIR__.'/Controller/UtilisateurController.php';
require_once __DIR__.'/Controller/CollecteController.php';
require_once __DIR__.'/Controller/InvitationController.php';
demarrerSession();
$auth = new AuthController();
$users = new UtilisateurController();
$collectes = new CollecteController();
$invitations = new InvitationController();
$page = (string)($_GET['page'] ?? 'accueil');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$errors = [];
$message = '';
$current = null;
$items = [];
$detail = null;
$slots = [];
$participations = [];
try {
    $current = utilisateur();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($page) {
            case 'inscription':
                $errors = $auth->inscription($_POST);
                if (!$errors) rediriger('connexion');
                break;
            case 'connexion':
                $message = $auth->connexion($_POST);
                if ($message === '') rediriger('accueil');
                break;
            case 'deconnexion': $auth->deconnexion();
            case 'utilisateur-sauver':
                $id = filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT) ?: 0;
                $errors = $users->enregistrer($_POST,$id ?: null);
                if (!$errors) rediriger('utilisateurs');
                $page = 'utilisateur-formulaire';
                if ($id) $detail = $users->trouver($id);
                break;
            case 'utilisateur-supprimer':
                $users->supprimer((int)($_POST['id'] ?? 0));
                rediriger('utilisateurs');
            case 'collecte-sauver':
                $id = filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT) ?: 0;
                $errors = $collectes->sauverCollecte($_POST,$id ?: null);
                if (!$errors) rediriger('gestion-collectes');
                $page = 'collecte-formulaire';
                if ($id) $detail = $collectes->detail($id);
                break;
            case 'creneau-sauver':
                $id = (int)($_POST['collecte_id'] ?? 0);
                $errors = $collectes->sauverCreneau($_POST);
                if (!$errors) rediriger('gestion-collectes');
                $page = 'collecte-gestion';
                break;
            case 'reserver':
                $collectes->reserver((int)($_POST['creneau_id'] ?? 0));
                rediriger('mes-participations');
            case 'annuler':
                $collectes->annuler((int)($_POST['id'] ?? 0));
                rediriger('mes-participations');
            case 'inviter-donneur':
                $id = (int)($_POST['collecte_id'] ?? 0);
                $invitations->inviter($_POST);
                header('Location: index.php?page=collecte-gestion&id='.$id); exit;
            case 'activer':
                $invitations->activer($_POST);
                rediriger('connexion');
            case 'sur-place':
                $id = (int)($_POST['collecte_id'] ?? 0);
                $collectes->enregistrerSurPlace($id,(int)($_POST['donneur_id'] ?? 0));
                header('Location: index.php?page=collecte-gestion&id='.$id); exit;
            case 'arriver':
                $id = (int)($_POST['collecte_id'] ?? 0);
                $collectes->arriver((int)($_POST['participation_id'] ?? 0));
                header('Location: index.php?page=collecte-gestion&id='.$id); exit;
            case 'confirmer-don':
                $id = (int)($_POST['collecte_id'] ?? 0);
                $collectes->confirmerDon((int)($_POST['participation_id'] ?? 0));
                header('Location: index.php?page=collecte-gestion&id='.$id); exit;
            default:
                http_response_code(404);
                $page = 'introuvable';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new RuntimeException('Méthode non autorisée.');
    }
    switch ($page) {
        case 'accueil':
        case 'collectes': $items = $collectes->publiques(); break;
        case 'collecte':
            $detail = $collectes->detail($id);
            if (!$detail) { http_response_code(404); $page='introuvable'; }
            else $slots = $collectes->creneaux($id);
            break;
        case 'mes-participations':
            $current = exigerConnexion();
            $participations = $collectes->mesParticipations((int)$current['id']);
            break;
        case 'utilisateurs': $items = $users->liste(); break;
        case 'utilisateur-formulaire':
            exigerRole(['ADMIN']);
            if ($id) $detail = $users->trouver($id);
            break;
        case 'gestion-collectes':
            $current = exigerRole(['RESPONSABLE','ADMIN']);
            $items = $collectes->administrables($current);
            break;
        case 'collecte-formulaire':
            $current = exigerRole(['RESPONSABLE','ADMIN']);
            if ($id) {
                $collectes->autoriserCollecte($id,$current);
                $detail = $collectes->detail($id);
            }
            break;
        case 'collecte-gestion':
            $current = exigerRole(['RESPONSABLE','ADMIN']);
            $collectes->autoriserCollecte($id,$current);
            $detail = $collectes->detail($id);
            $slots = $collectes->creneaux($id);
            $participations = $collectes->participationsCollecte($id,$current);
            break;
        case 'connexion': case 'inscription': case 'activer': case 'introuvable': break;
        default: http_response_code(404); $page='introuvable';
    }
} catch (Throwable $error) {
    if ($error instanceof PDOException) {
        http_response_code(503);
        $message = 'Base de données indisponible. Vérifiez la configuration et importez database.sql.';
    } else {
        $message = $error->getMessage();
    }
    if (!in_array($page,['connexion','inscription','utilisateur-formulaire','collecte-formulaire','collecte-gestion'],true)) $page='erreur';
}
$adminPage = in_array($page,['utilisateurs','utilisateur-formulaire','gestion-collectes','collecte-formulaire','collecte-gestion'],true);
ob_start();
require __DIR__.($adminPage ? '/View/back-office/page.php' : '/View/front-office/page.php');
$content = ob_get_clean();
require __DIR__.($adminPage ? '/View/back-office/layout.php' : '/View/front-office/layout.php');
