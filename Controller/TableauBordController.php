<?php
declare(strict_types=1);

final class TableauBordController
{
    public function chiffres(): array
    {
        exigerRole(['ADMIN']);
        $db=connexion();
        return [
            'donneurs'=>(int)$db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='DONNEUR' AND actif=1")->fetchColumn(),
            'responsables'=>(int)$db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='RESPONSABLE' AND actif=1")->fetchColumn(),
            'collectes'=>(int)$db->query('SELECT COUNT(*) FROM collectes')->fetchColumn(),
            'reservations'=>(int)$db->query("SELECT COUNT(*) FROM participations WHERE mode='EN_LIGNE'")->fetchColumn(),
            'visites_sur_place'=>(int)$db->query("SELECT COUNT(*) FROM participations WHERE mode='SUR_PLACE'")->fetchColumn(),
            'dons_confirmes'=>(int)$db->query('SELECT COUNT(*) FROM dons_sang')->fetchColumn(),
            'volume_ml'=>(int)$db->query('SELECT COALESCE(SUM(volume_ml),0) FROM dons_sang')->fetchColumn(),
            'sos_actives'=>(int)$db->query('SELECT COUNT(*) FROM alertes_sos WHERE expire_le>NOW() AND supprime_le IS NULL')->fetchColumn(),
            'pages_vues'=>(int)$db->query('SELECT COALESCE(SUM(vues),0) FROM statistiques_jour')->fetchColumn(),
        ];
    }
    public function compterVue(): void
    {
        $q=connexion()->prepare('INSERT INTO statistiques_jour (jour,vues) VALUES (CURRENT_DATE,1) ON DUPLICATE KEY UPDATE vues=vues+1');
        $q->execute();
    }
}
