<?php
/**
 * PharmacieService: ALL the SQL of the Pharmacies module (prepared statements only).
 * Main JOIN: pharmacies on duty today that declare a medicine in stock
 * (etablissements_sante + gardes_pharmacie + disponibilites + medicaments).
 */
class PharmacieService
{
    private PDO $db;
    private const DUTY_TODAY = 'CURDATE() BETWEEN g.start_date AND g.end_date';

    public function __construct() { $this->db = Database::getConnexion(); }

    // ---------------------------------------------------------------- pharmacies (read only)
    /** @return EtablissementSante[] */
    public function pharmacies(): array
    {
        $rows = $this->db->query("SELECT * FROM etablissements_sante WHERE type = 'pharmacie' ORDER BY name")->fetchAll();
        return array_map(fn(array $r) => EtablissementSante::fromArray($r), $rows);
    }

    public function cities(): array
    {
        return $this->db->query("SELECT DISTINCT city FROM etablissements_sante WHERE type = 'pharmacie' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
    }

    private function dutyUntil(): string
    {
        return '(SELECT MAX(g.end_date) FROM gardes_pharmacie g WHERE g.etablissement_id = e.id AND ' . self::DUTY_TODAY . ')';
    }

    private function onDutyFilter(): string
    {
        return ' AND EXISTS (SELECT 1 FROM gardes_pharmacie g WHERE g.etablissement_id = e.id AND ' . self::DUTY_TODAY . ')';
    }

    /** Public list of pharmacies with their duty status. @return array[] */
    public function listPharmacies(string $city, bool $onDuty): array
    {
        $sql = 'SELECT e.*, ' . $this->dutyUntil() . " AS duty_until FROM etablissements_sante e WHERE e.type = 'pharmacie'";
        $p = [];
        if ($city !== '') { $sql .= ' AND e.city = :city'; $p['city'] = $city; }
        if ($onDuty) $sql .= $this->onDutyFilter();
        $stmt = $this->db->prepare($sql . ' ORDER BY e.name');
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    /** The module JOIN: pharmacies (on duty today if asked) declaring a matching medicine in stock. @return array[] */
    public function searchStock(string $q, string $city, bool $onDuty): array
    {
        $sql = 'SELECT e.id, e.name, e.city, e.address, e.phone, m.name AS med_name, m.dosage, m.form, d.quantity, ' . $this->dutyUntil() . ' AS duty_until
                FROM etablissements_sante e
                JOIN disponibilites d ON d.etablissement_id = e.id AND d.quantity > 0
                JOIN medicaments m ON m.id = d.medicament_id
                WHERE e.type = \'pharmacie\' AND (m.name LIKE :q1 OR m.dci LIKE :q2)';
        $p = ['q1' => '%' . $q . '%', 'q2' => '%' . $q . '%'];
        if ($city !== '') { $sql .= ' AND e.city = :city'; $p['city'] = $city; }
        if ($onDuty) $sql .= $this->onDutyFilter();
        $stmt = $this->db->prepare($sql . ' ORDER BY e.name, m.name');
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    // ---------------------------------------------------------------- medicaments (CRUD)
    /** @return Medicament[] */
    public function medicaments(string $q = ''): array
    {
        $stmt = $this->db->prepare('SELECT * FROM medicaments WHERE name LIKE :q1 OR dci LIKE :q2 ORDER BY name, dosage');
        $stmt->execute(['q1' => '%' . $q . '%', 'q2' => '%' . $q . '%']);
        return array_map(fn(array $r) => Medicament::fromArray($r), $stmt->fetchAll());
    }

    public function findMedicament(int $id): ?Medicament
    {
        $stmt = $this->db->prepare('SELECT * FROM medicaments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $r = $stmt->fetch();
        return $r ? Medicament::fromArray($r) : null;
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

    public function deleteMedicament(int $id): void { $this->db->prepare('DELETE FROM medicaments WHERE id = :id')->execute(['id' => $id]); }

    // ---------------------------------------------------------------- gardes (CRUD)
    /** @return GardePharmacie[] */
    public function gardes(): array
    {
        $rows = $this->db->query('SELECT g.*, e.name AS etablissement_name FROM gardes_pharmacie g JOIN etablissements_sante e ON e.id = g.etablissement_id ORDER BY g.start_date DESC, e.name')->fetchAll();
        return array_map(fn(array $r) => GardePharmacie::fromArray($r), $rows);
    }

    public function findGarde(int $id): ?GardePharmacie
    {
        $stmt = $this->db->prepare('SELECT g.*, e.name AS etablissement_name FROM gardes_pharmacie g JOIN etablissements_sante e ON e.id = g.etablissement_id WHERE g.id = :id');
        $stmt->execute(['id' => $id]);
        $r = $stmt->fetch();
        return $r ? GardePharmacie::fromArray($r) : null;
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

    public function deleteGarde(int $id): void { $this->db->prepare('DELETE FROM gardes_pharmacie WHERE id = :id')->execute(['id' => $id]); }

    // ---------------------------------------------------------------- disponibilites (stock)
    /** @return Disponibilite[] */
    public function disponibilites(): array
    {
        $rows = $this->db->query('SELECT d.*, e.name AS etablissement_name, m.name AS medicament_name FROM disponibilites d
                                  JOIN etablissements_sante e ON e.id = d.etablissement_id JOIN medicaments m ON m.id = d.medicament_id
                                  ORDER BY e.name, m.name')->fetchAll();
        return array_map(fn(array $r) => Disponibilite::fromArray($r), $rows);
    }

    /** Creates or updates the stock line of (pharmacy, medicine). */
    public function setStock(int $etabId, int $medId, int $qty): void
    {
        $this->db->prepare('INSERT INTO disponibilites (etablissement_id, medicament_id, quantity) VALUES (:e, :m, :q)
                            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)')->execute(['e' => $etabId, 'm' => $medId, 'q' => $qty]);
    }

    public function deleteStock(int $id): void { $this->db->prepare('DELETE FROM disponibilites WHERE id = :id')->execute(['id' => $id]); }

    public function onDutyCount(): int
    {
        return (int)$this->db->query('SELECT COUNT(DISTINCT g.etablissement_id) FROM gardes_pharmacie g WHERE ' . self::DUTY_TODAY)->fetchColumn();
    }

    public function countMedicaments(): int { return (int)$this->db->query('SELECT COUNT(*) FROM medicaments')->fetchColumn(); }
}
