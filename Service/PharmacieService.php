<?php
declare(strict_types=1);

/**
 * PharmacieService : requêtes PDO préparées du module Pharmacies & Médicaments
 * reliées à la table partagée etablissements_sante (type = 'pharmacie').
 */
final class PharmacieService
{
    private PDO $db;
    private const DUTY_TODAY = 'CURDATE() BETWEEN g.start_date AND g.end_date';

    public function __construct()
    {
        $this->db = Database::getConnexion();
    }

    /** @return EtablissementSante[] */
    public function pharmacies(): array
    {
        $rows = $this->db->query("SELECT * FROM etablissements_sante WHERE type = 'pharmacie' ORDER BY nom ASC")->fetchAll();
        return array_map(static fn(array $r): EtablissementSante => EtablissementSante::fromArray($r), $rows);
    }

    public function cities(): array
    {
        return $this->db->query("SELECT DISTINCT ville FROM etablissements_sante WHERE type = 'pharmacie' ORDER BY ville ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    private function dutyUntil(): string
    {
        return '(SELECT MAX(g.end_date) FROM gardes_pharmacie g WHERE g.etablissement_id = e.id AND ' . self::DUTY_TODAY . ')';
    }

    private function onDutyFilter(): string
    {
        return ' AND EXISTS (SELECT 1 FROM gardes_pharmacie g WHERE g.etablissement_id = e.id AND ' . self::DUTY_TODAY . ')';
    }

    public function listPharmacies(string $city = '', bool $onDuty = false): array
    {
        $sql = 'SELECT e.*, ' . $this->dutyUntil() . " AS duty_until,
                       (SELECT COUNT(*) FROM disponibilites d WHERE d.etablissement_id = e.id AND d.quantity > 0) AS nb_meds_dispo
                FROM etablissements_sante e WHERE e.type = 'pharmacie'";
        $p = [];
        if ($city !== '') {
            $sql .= ' AND e.ville = :city';
            $p['city'] = $city;
        }
        if ($onDuty) {
            $sql .= $this->onDutyFilter();
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY (duty_until IS NULL) ASC, e.nom ASC');
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function searchStock(string $q, string $city = '', bool $onDuty = false): array
    {
        $sql = 'SELECT e.id, e.nom, e.ville, e.adresse, e.telephone, m.id AS med_id, m.name AS med_name, m.dci, m.dosage, m.form, m.requires_prescription, d.quantity, ' . $this->dutyUntil() . " AS duty_until
                FROM etablissements_sante e
                JOIN disponibilites d ON d.etablissement_id = e.id AND d.quantity > 0
                JOIN medicaments m ON m.id = d.medicament_id
                WHERE e.type = 'pharmacie' AND (m.name LIKE :q1 OR m.dci LIKE :q2)";
        $p = ['q1' => '%' . $q . '%', 'q2' => '%' . $q . '%'];
        if ($city !== '') {
            $sql .= ' AND e.ville = :city';
            $p['city'] = $city;
        }
        if ($onDuty) {
            $sql .= $this->onDutyFilter();
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY (duty_until IS NULL) ASC, e.nom ASC, m.name ASC');
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    /** @return Medicament[] */
    public function medicaments(string $q = ''): array
    {
        $stmt = $this->db->prepare('SELECT * FROM medicaments WHERE name LIKE :q1 OR COALESCE(dci,"") LIKE :q2 ORDER BY name ASC, dosage ASC');
        $stmt->execute(['q1' => '%' . $q . '%', 'q2' => '%' . $q . '%']);
        return array_map(static fn(array $r): Medicament => Medicament::fromArray($r), $stmt->fetchAll());
    }

    public function medicamentExists(string $name, ?string $dosage, string $form, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM medicaments WHERE name = :n AND dosage <=> :d AND form = :f AND id <> :id LIMIT 1');
        $stmt->execute(['n' => $name, 'd' => $dosage, 'f' => $form, 'id' => $exceptId]);
        return (bool)$stmt->fetch();
    }

    public function saveMedicament(Medicament $m): void
    {
        $p = ['n' => $m->getName(), 'dci' => $m->getDci(), 'f' => $m->getForm(), 'd' => $m->getDosage(), 'rx' => $m->getRequiresPrescription() ? 1 : 0];
        if ($m->getId() > 0) {
            $this->db->prepare('UPDATE medicaments SET name = :n, dci = :dci, form = :f, dosage = :d, requires_prescription = :rx WHERE id = :id')->execute($p + ['id' => $m->getId()]);
        } else {
            $this->db->prepare('INSERT INTO medicaments (name, dci, form, dosage, requires_prescription) VALUES (:n, :dci, :f, :d, :rx)')->execute($p);
        }
    }

    public function deleteMedicament(int $id): void
    {
        $this->db->prepare('DELETE FROM medicaments WHERE id = :id')->execute(['id' => $id]);
    }

    /** @return GardePharmacie[] */
    public function gardes(): array
    {
        $rows = $this->db->query('SELECT g.*, e.nom AS etablissement_name FROM gardes_pharmacie g JOIN etablissements_sante e ON e.id = g.etablissement_id ORDER BY g.start_date DESC, e.nom ASC')->fetchAll();
        return array_map(static fn(array $r): GardePharmacie => GardePharmacie::fromArray($r), $rows);
    }

    public function gardeOverlaps(int $etabId, string $start, string $end, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM gardes_pharmacie WHERE etablissement_id = :e AND start_date <= :en AND end_date >= :st AND id <> :id LIMIT 1');
        $stmt->execute(['e' => $etabId, 'st' => $start, 'en' => $end, 'id' => $exceptId]);
        return (bool)$stmt->fetch();
    }

    public function saveGarde(GardePharmacie $g): void
    {
        $p = ['e' => $g->getEtablissementId(), 's' => $g->getStartDate(), 'en' => $g->getEndDate(), 'n' => $g->getNotes()];
        if ($g->getId() > 0) {
            $this->db->prepare('UPDATE gardes_pharmacie SET etablissement_id = :e, start_date = :s, end_date = :en, notes = :n WHERE id = :id')->execute($p + ['id' => $g->getId()]);
        } else {
            $this->db->prepare('INSERT INTO gardes_pharmacie (etablissement_id, start_date, end_date, notes) VALUES (:e, :s, :en, :n)')->execute($p);
        }
    }

    public function deleteGarde(int $id): void
    {
        $this->db->prepare('DELETE FROM gardes_pharmacie WHERE id = :id')->execute(['id' => $id]);
    }

    /** @return Disponibilite[] */
    public function disponibilites(): array
    {
        $rows = $this->db->query('SELECT d.*, e.nom AS etablissement_name, m.name AS medicament_name
                                  FROM disponibilites d
                                  JOIN etablissements_sante e ON e.id = d.etablissement_id
                                  JOIN medicaments m ON m.id = d.medicament_id
                                  ORDER BY e.nom ASC, m.name ASC')->fetchAll();
        return array_map(static fn(array $r): Disponibilite => Disponibilite::fromArray($r), $rows);
    }

    public function setStock(int $etabId, int $medId, int $qty): void
    {
        $this->db->prepare('INSERT INTO disponibilites (etablissement_id, medicament_id, quantity) VALUES (:e, :m, :q)
                            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)')->execute(['e' => $etabId, 'm' => $medId, 'q' => $qty]);
    }

    public function deleteStock(int $id): void
    {
        $this->db->prepare('DELETE FROM disponibilites WHERE id = :id')->execute(['id' => $id]);
    }
}
