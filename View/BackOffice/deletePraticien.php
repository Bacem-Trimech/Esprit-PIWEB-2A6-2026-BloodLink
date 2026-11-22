<?php
require_once __DIR__ . '/../../Controller/PraticienController.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $controller = new PraticienController();
    $controller->supprimerPraticien($id);
}

header('Location: listPraticiens.php');
exit;
?>