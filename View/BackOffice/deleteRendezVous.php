<?php
require_once __DIR__ . '/../../Controller/RendezVousController.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $controller = new RendezVousController();
    $controller->supprimerRendezVous($id);
}

header('Location: listRendezVous.php');
exit;
?>