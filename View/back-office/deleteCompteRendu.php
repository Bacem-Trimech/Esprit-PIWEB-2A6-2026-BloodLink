<?php
include_once '../../controller/compteRenduAnalyseC.php';
$crC = new CompteRenduAnalyseC();
$crC->supprimerCompteRendu($_GET['id']);
header('Location: compteRenduList.php');
