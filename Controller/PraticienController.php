
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Praticien.php';

class PraticienController
{
    public function listerPraticiens()
    {
        $sql = "SELECT * FROM Praticien ORDER BY id_praticien DESC";

        try {
            return config::getConnexion()->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function ajouterPraticien(Praticien $praticien): void
    {
        $sql = "INSERT INTO Praticien
                (nom, prenom, specialite, telephone, email, adresse)
                VALUES (:nom, :prenom, :specialite, :telephone, :email, :adresse)";

        try {
            $req = config::getConnexion()->prepare($sql);
            $req->execute([
                'nom' => $praticien->getNom(),
                'prenom' => $praticien->getPrenom(),
                'specialite' => $praticien->getSpecialite(),
                'telephone' => $praticien->getTelephone(),
                'email' => $praticien->getEmail(),
                'adresse' => $praticien->getAdresse()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function recupererPraticien(int $id)
    {
        $sql = "SELECT * FROM Praticien WHERE id_praticien = :id";
        $req = config::getConnexion()->prepare($sql);
        $req->execute(['id' => $id]);

        return $req->fetch();
    }

    public function modifierPraticien(Praticien $praticien): void
    {
        $sql = "UPDATE Praticien SET
                nom=:nom, prenom=:prenom, specialite=:specialite,
                telephone=:telephone, email=:email, adresse=:adresse
                WHERE id_praticien=:id";

        $req = config::getConnexion()->prepare($sql);
        $req->execute([
            'nom' => $praticien->getNom(),
            'prenom' => $praticien->getPrenom(),
            'specialite' => $praticien->getSpecialite(),
            'telephone' => $praticien->getTelephone(),
            'email' => $praticien->getEmail(),
            'adresse' => $praticien->getAdresse(),
            'id' => $praticien->getId()
        ]);
    }

    public function supprimerPraticien(int $id): void
    {
        $sql = "DELETE FROM Praticien WHERE id_praticien = :id";
        $req = config::getConnexion()->prepare($sql);
        $req->execute(['id' => $id]);
    }
}
?>
