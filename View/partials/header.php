<?php
// Variables attendues : $pageTitle (string), $active (dashboard|analyses|comptesrendus|consulter)
include_once __DIR__ . '/icons.php';
$root = '..';
function navItem($key, $active, $href, $icon, $label) {
    $cls = ($key == $active) ? ' class="active"' : '';
    echo '<a' . $cls . ' href="' . $href . '">' . icon($icon) . '<span>' . $label . '</span></a>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?> - BloodLink</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?php echo $root; ?>/assets/style.css">
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <span class="brand-logo"><?php echo icon('heart'); ?></span>
            <span>BloodLink</span>
        </div>
        <nav class="nav">
            <?php navItem('dashboard', $active, $root . '/backoffice/dashboard.php', 'dashboard', 'Dashboard'); ?>
            <div class="nav-title">Laboratoires</div>
            <?php navItem('analyses', $active, $root . '/backoffice/analyseList.php', 'flask', 'Analyses médicales'); ?>
            <?php navItem('comptesrendus', $active, $root . '/backoffice/compteRenduList.php', 'report', 'Comptes rendus'); ?>
            <?php navItem('consulter', $active, $root . '/frontoffice/consulterCompteRendu.php', 'search', 'Consulter un compte rendu'); ?>
        </nav>
        <div class="sidebar-foot">&copy; 2026 BloodLink<br>v2.0.0</div>
    </aside>
    <div class="main">
        <div class="topbar"><?php echo htmlspecialchars($pageTitle); ?></div>
        <div class="content">
