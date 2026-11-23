<?php
require_once __DIR__ . '/../Model/SessionCollection.php';
class ModuleController {
    private SessionCollection $store;
    private array $definitions;
    public function __construct() {
        $this->store = new SessionCollection();
        $this->definitions = [
            'services'=>['key'=>'services_medicaux','title'=>'Services médicaux','fields'=>['etablissement'=>'Établissement','nom'=>'Nom du service','specialite'=>'Spécialité','description'=>'Description','horaires'=>'Horaires','telephone'=>'Téléphone','statut'=>'Statut'],'required'=>['etablissement','nom','specialite']],
            'ressources'=>['key'=>'ressources_medicales','title'=>'Ressources médicales','fields'=>['etablissement'=>'Établissement','nom'=>'Nom de la ressource','type'=>'Type','description'=>'Description','quantite'=>'Quantité','etat'=>'État'],'required'=>['etablissement','nom','type','etat']],
            'avis'=>['key'=>'avis_etablissements','title'=>'Avis','fields'=>['etablissement'=>'Établissement','auteur'=>'Nom affiché','note'=>'Note sur 5','commentaire'=>'Commentaire','statut'=>'Statut'],'required'=>['etablissement','auteur','note','commentaire']],
            'signalements'=>['key'=>'signalements','title'=>'Signalements','fields'=>['etablissement'=>'Établissement','motif'=>'Motif','description'=>'Description','statut'=>'Statut','reponse'=>'Réponse administrative'],'required'=>['etablissement','motif','description']],
            'sommeil'=>['key'=>'gs_sommeil','title'=>'Suivi du sommeil','fields'=>['date'=>'Date','heureCoucher'=>'Heure du coucher','heureReveil'=>'Heure du réveil','duree'=>'Durée calculée (heures)','note'=>'Note personnelle'],'required'=>['date','heureCoucher','heureReveil']],
            'activites'=>['key'=>'gs_activites','title'=>'Activités sportives','fields'=>['date'=>'Date','type'=>'Type d’activité','duree'=>'Durée (minutes)','distance'=>'Distance (km, facultatif)','description'=>'Description'],'required'=>['date','type','duree']],
            'medicaments'=>['key'=>'gs_medicaments','title'=>'Suivi des médicaments','fields'=>['nom'=>'Nom du médicament','instructions'=>'Instructions reçues','dateDebut'=>'Date de début','dateFin'=>'Date de fin','heuresRappel'=>'Heures de rappel','statut'=>'Statut'],'required'=>['nom','instructions','dateDebut']],
            'cycle'=>['key'=>'gs_cycle','title'=>'Suivi du cycle menstruel','fields'=>['dateDebut'=>'Début du cycle','dateFin'=>'Fin des règles','symptomes'=>'Symptômes (facultatif)','notes'=>'Notes (facultatif)'],'required'=>['dateDebut']],
            'habitudes'=>['key'=>'gs_habitudes','title'=>'Habitudes quotidiennes','fields'=>['nom'=>'Habitude','objectif'=>'Objectif','date'=>'Date','progression'=>'Progression','statut'=>'Statut'],'required'=>['nom','objectif','date']],
        ];
    }
    private function admin(): bool { return ($_SESSION['user_role'] ?? '') === 'admin'; }
    private function needAdmin(): void { if (!$this->admin()) { header('Location: index.php?action=admin-login'); exit; } }
    private function csrf(): void { if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) { http_response_code(403); exit('Jeton CSRF invalide.'); } }
    private function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
    private function definition(string $type): array { if (!isset($this->definitions[$type])) { http_response_code(404); exit('Fonctionnalité inconnue.'); } return $this->definitions[$type]; }
    private function rowsFor(string $type): array {
        $def=$this->definition($type); $rows=$this->store->all($def['key']);
        if (in_array($type,['sommeil','activites','medicaments','cycle','habitudes'],true)) $rows=array_values(array_filter($rows,fn($r)=>($r['owner']??'')===session_id()));
        return $rows;
    }
    private function establishments(): array { return (new EtablissementSante())->all(); }
    public function adminModule(): void {
        $this->needAdmin(); $type=$_GET['type']??'services'; $def=$this->definition($type); $rows=$this->store->all($def['key']);
        $message=$_SESSION['flash']??''; unset($_SESSION['flash']); $isAdmin=true; require __DIR__.'/../View/shared/list.php';
    }
    public function moduleForm(): void {
        $type=$_GET['type']??'services'; $def=$this->definition($type); $personal=in_array($type,['sommeil','activites','medicaments','cycle','habitudes'],true);
        if (!$personal && !in_array($type,['avis','signalements'],true)) $this->needAdmin();
        if (in_array($type,['avis','signalements'],true) && !isset($_SESSION['demo_user'])) $_SESSION['demo_user']='Utilisateur de démonstration';
        $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT); $existing=$id?$this->store->find($def['key'],(int)$id):null;
        if ($existing && ($personal || in_array($type,['avis','signalements'],true)) && ($existing['owner']??'')!==session_id() && !$this->admin()) { http_response_code(403); exit('Accès refusé.'); }
        $data=$existing??[]; $errors=[];
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $this->csrf(); $data=[]; foreach($def['fields'] as $k=>$label) $data[$k]=trim((string)($_POST[$k]??'')); if ($existing && isset($existing['owner'])) $data['owner']=$existing['owner'];
            foreach($def['required'] as $k) if ($data[$k]==='') $errors[$k]='Champ obligatoire.';
            if (in_array($type,['avis'],true) && ($data['note']!=='' && (!ctype_digit($data['note']) || (int)$data['note']<1 || (int)$data['note']>5))) $errors['note']='La note doit être comprise entre 1 et 5.';
            foreach (['quantite','duree','distance','progression'] as $numeric) if (isset($data[$numeric]) && $data[$numeric]!=='' && (!is_numeric($data[$numeric]) || (float)$data[$numeric]<0)) $errors[$numeric]='Valeur numérique invalide.';
            foreach (['date','dateDebut','dateFin'] as $dateKey) if (!empty($data[$dateKey]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$data[$dateKey])) $errors[$dateKey]='Date invalide.';
            if (!$errors) {
                if ($type==='sommeil' && $data['heureCoucher']!=='' && $data['heureReveil']!=='') { $start=strtotime($data['heureCoucher']); $end=strtotime($data['heureReveil']); if ($end <= $start) $end += 86400; $data['duree']=number_format(($end-$start)/3600, 2, '.', ''); }
                if ($personal) $data['owner']=session_id();
                if ($type==='avis') { $data['statut']='En attente de modération'; $data['owner']=session_id(); }
                if ($type==='signalements') { $data['statut']='En attente'; $data['owner']=session_id(); }
                if ($id) $this->store->update($def['key'],(int)$id,$data); else $this->store->create($def['key'],$data);
                $_SESSION['flash']='Enregistrement effectué.';
                $target=$personal?'personal&type='.$type:(in_array($type,['avis','signalements'],true)?'index':('admin-module&type='.$type)); header('Location: index.php?action='.$target); exit;
            }
        }
        $title=$def['title']; $csrfToken=$_SESSION['csrf_token']; $establishments=$this->establishments(); $formFields=$def['fields']; if ($type==='avis') unset($formFields['statut']); if ($type==='signalements') { unset($formFields['statut'],$formFields['reponse']); }
        require __DIR__.'/../View/shared/form.php';
    }
    public function delete(): void {
        $type=$_GET['type']??''; $def=$this->definition($type); $personal=in_array($type,['sommeil','activites','medicaments','cycle','habitudes'],true);
        if (!$personal) $this->needAdmin(); if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit('Méthode non autorisée.'); }
        $this->csrf(); $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT); $row=$id?$this->store->find($def['key'],(int)$id):null;
        if ($personal && (!$row || ($row['owner']??'')!==session_id())) { http_response_code(403); exit('Accès refusé.'); }
        if ($id) $this->store->delete($def['key'],(int)$id); $_SESSION['flash']='Enregistrement supprimé.';
        header('Location: index.php?action='.($personal?'personal&type='.$type:'admin-module&type='.$type)); exit;
    }
    public function submitPublic(): void {
        $type=$_GET['type']??'avis'; if (!in_array($type,['avis','signalements'],true)) { http_response_code(404); exit('Introuvable'); }
        $this->moduleForm();
    }
    public function personal(): void {
        if (!isset($_SESSION['demo_user'])) $_SESSION['demo_user']='Utilisateur de démonstration';
        $type=$_GET['type']??'sommeil'; $def=$this->definition($type); if (!in_array($type,['sommeil','activites','medicaments','cycle','habitudes'],true)) { http_response_code(404); exit('Introuvable'); }
        $rows=$this->rowsFor($type); $message=$_SESSION['flash']??''; unset($_SESSION['flash']); $isAdmin=false; require __DIR__.'/../View/shared/list.php';
    }
    public function publicModule(): void {
        $type=$_GET['type']??'services'; if (!in_array($type,['services','ressources','avis'],true)) { http_response_code(404); exit('Introuvable'); }
        $def=$this->definition($type); $rows=$this->store->all($def['key']);
        if ($type==='avis') $rows=array_values(array_filter($rows,fn($r)=>($r['statut']??'')==='Publié'));
        $isAdmin=false; $message=''; require __DIR__.'/../View/shared/public-list.php';
    }
    public function dashboard(): void {
        if (!isset($_SESSION['demo_user'])) $_SESSION['demo_user']='Utilisateur de démonstration';
        $counts=[]; foreach(['sommeil','activites','medicaments','cycle','habitudes'] as $t) $counts[$t]=count($this->rowsFor($t)); $medicaments=$this->rowsFor('medicaments');
        require __DIR__.'/../View/front-office/gestionnaire-sante/dashboard.php';
    }
}
