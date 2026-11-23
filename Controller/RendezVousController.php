
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Model/RendezVous.php';

class RendezVousController
{
    public function listerRendezVous()
    {
        $sql = "SELECT r.*, p.nom, p.prenom, p.specialite
                FROM RendezVous r
                INNER JOIN Praticien p
                ON r.id_praticien = p.id_praticien
                ORDER BY r.id_rendezvous DESC";

        try {
            return config::getConnexion()->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function ajouterRendezVous(RendezVous $rdv): void
    {
        $sql = "INSERT INTO RendezVous
                (date_rendezvous, heure_rendezvous, motif, statut, id_praticien)
                VALUES (:date, :heure, :motif, :statut, :id_praticien)";

        try {
            $req = config::getConnexion()->prepare($sql);
            $req->execute([
                'date' => $rdv->getDateRendezVous(),
                'heure' => $rdv->getHeureRendezVous(),
                'motif' => $rdv->getMotif(),
                'statut' => $rdv->getStatut(),
                'id_praticien' => $rdv->getIdPraticien()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function recupererRendezVous(int $id)
    {
        $sql = "SELECT * FROM RendezVous WHERE id_rendezvous = :id";
        $req = config::getConnexion()->prepare($sql);
        $req->execute(['id' => $id]);

        return $req->fetch();
    }

    public function modifierRendezVous(RendezVous $rdv): void
    {
        $sql = "UPDATE RendezVous SET
                date_rendezvous=:date,
                heure_rendezvous=:heure,
                motif=:motif,
                statut=:statut,
                id_praticien=:id_praticien
                WHERE id_rendezvous=:id";

        $req = config::getConnexion()->prepare($sql);
        $req->execute([
            'date' => $rdv->getDateRendezVous(),
            'heure' => $rdv->getHeureRendezVous(),
            'motif' => $rdv->getMotif(),
            'statut' => $rdv->getStatut(),
            'id_praticien' => $rdv->getIdPraticien(),
            'id' => $rdv->getId()
        ]);
    }

    public function supprimerRendezVous(int $id): void
    {
        $sql = "DELETE FROM RendezVous WHERE id_rendezvous = :id";
        $req = config::getConnexion()->prepare($sql);
        $req->execute(['id' => $id]);
    }
}
?>
