<?php
include_once '../../controller/analyseMedicaleC.php';
$analyseC = new AnalyseMedicaleC();
$analyseC->supprimerAnalyse($_GET['id']);
header('Location: analyseList.php');
