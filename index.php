<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/Model/EtablissementSante.php';
require_once __DIR__ . '/Controller/EtablissementSanteController.php';
require_once __DIR__ . '/Controller/ModuleController.php';
$controller = new EtablissementSanteController();
$modules = new ModuleController();
$action = $_GET['action'] ?? 'index';
switch ($action) {
    case 'details': $controller->details(); break;
    case 'admin-login': $controller->adminLogin(); break;
    case 'admin-logout': $controller->adminLogout(); break;
    case 'admin-index': $controller->adminIndex(); break;
    case 'admin-create': $controller->adminCreate(); break;
    case 'admin-edit': $controller->adminEdit(); break;
    case 'admin-delete': $controller->adminDelete(); break;
    case 'admin-module': $modules->adminModule(); break;
    case 'public-module': $modules->publicModule(); break;
    case 'module-form': $modules->moduleForm(); break;
    case 'module-delete': $modules->delete(); break;
    case 'submit-public': $modules->submitPublic(); break;
    case 'dashboard': $modules->dashboard(); break;
    case 'personal': $modules->personal(); break;
    default: $controller->index(); break;
}
