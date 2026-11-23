<?php
class EtablissementSanteController {
    private EtablissementSante $model;

    public function __construct() {
        $this->model = new EtablissementSante();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function isAdmin(): bool {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    private function requireAdmin(): void {
        if (!$this->isAdmin()) {
            header('Location: index.php?action=admin-login');
            exit;
        }
    }

    private function checkCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            exit('Requête refusée : jeton de sécurité invalide.');
        }
    }

    public function index(): void {
        $etablissements = $this->model->all();
        $message = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../View/front-office/etablissements/index.php';
    }

    public function details(): void {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $etablissement = $id ? $this->model->find($id) : null;
        if (!$etablissement) {
            http_response_code(404);
            exit('Établissement introuvable.');
        }
        require __DIR__ . '/../View/front-office/etablissements/details.php';
    }

    public function adminLogin(): void {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            if (hash_equals(DEMO_ADMIN_USERNAME, $username) && hash_equals(DEMO_ADMIN_PASSWORD, $password)) {
                session_regenerate_id(true);
                $_SESSION['user_role'] = 'admin';
                $_SESSION['flash'] = 'Bienvenue dans le Back Office.';
                header('Location: index.php?action=admin-index');
                exit;
            }
            $error = 'Identifiants incorrects.';
        }
        require __DIR__ . '/../View/back-office/etablissements/login.php';
    }

    public function adminLogout(): void {
        unset($_SESSION['user_role']);
        $_SESSION['flash'] = 'Déconnexion effectuée.';
        header('Location: index.php');
        exit;
    }

    public function adminIndex(): void {
        $this->requireAdmin();
        $etablissements = $this->model->all();
        $message = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../View/back-office/etablissements/index.php';
    }

    public function adminCreate(): void {
        $this->requireAdmin();
        $this->showForm(null);
    }

    public function adminEdit(): void {
        $this->requireAdmin();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $found = $id ? $this->model->find($id) : null;
        if (!$found) {
            http_response_code(404);
            exit('Établissement introuvable.');
        }
        $this->showForm($found, (int)$id);
    }

    private function showForm(?array $existing, ?int $id = null): void {
        $data = $existing ?? ['nom'=>'','type'=>'','ville'=>'','adresse'=>'','telephone'=>'','email'=>'','description'=>''];
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
            $data = $this->readForm();
            $errors = $this->validate($data);
            if (!$errors) {
                if ($id === null) {
                    $this->model->create($data);
                    $_SESSION['flash'] = 'Établissement ajouté avec succès.';
                } else {
                    $this->model->update($id, $data);
                    $_SESSION['flash'] = 'Établissement modifié avec succès.';
                }
                header('Location: index.php?action=admin-index');
                exit;
            }
        }
        $pageTitle = $id === null ? 'Ajouter un établissement' : 'Modifier un établissement';
        $formAction = $id === null ? 'index.php?action=admin-create' : 'index.php?action=admin-edit&id=' . $id;
        require __DIR__ . '/../View/back-office/etablissements/form.php';
    }

    public function adminDelete(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }
        $this->checkCsrf();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $this->model->delete((int)$id);
            $_SESSION['flash'] = 'Établissement supprimé.';
        }
        header('Location: index.php?action=admin-index');
        exit;
    }

    private function readForm(): array {
        $keys = ['nom','type','ville','adresse','telephone','email','description'];
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = trim($_POST[$key] ?? '');
        }
        return $data;
    }

    private function validate(array $data): array {
        $errors = [];
        foreach (['nom','type','ville','adresse','telephone','email'] as $key) {
            if ($data[$key] === '') $errors[$key] = 'Ce champ est obligatoire.';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }
        if ($data['telephone'] !== '' && !preg_match('/^[0-9 +().-]{8,20}$/', $data['telephone'])) {
            $errors['telephone'] = 'Téléphone invalide.';
        }
        return $errors;
    }
}
