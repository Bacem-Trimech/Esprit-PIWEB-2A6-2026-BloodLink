<?php
declare(strict_types=1);

final class PharmacieController
{
    private PharmacieService $service;

    public function __construct()
    {
        $this->service = new PharmacieService();
    }

    public function donneesPubliques(string $city = '', bool $onDuty = false, string $q = ''): array
    {
        return [
            'cities' => $this->service->cities(),
            'pharmacies' => $this->service->listPharmacies($city, $onDuty),
            'medicaments' => $this->service->medicaments($q),
            'stockResults' => $q !== '' ? $this->service->searchStock($q, $city, $onDuty) : [],
        ];
    }

    public function donneesAdmin(): array
    {
        exigerRole(['ADMIN']);
        return [
            'pharmacies' => $this->service->pharmacies(),
            'medicaments' => $this->service->medicaments(),
            'gardes' => $this->service->gardes(),
            'disponibilites' => $this->service->disponibilites(),
        ];
    }

    public function sauverMedicament(array $post, int $id = 0): array
    {
        exigerRole(['ADMIN']);
        verifierCsrf();

        $name = trim((string)($post['name'] ?? ''));
        $dci = trim((string)($post['dci'] ?? ''));
        $form = trim((string)($post['form'] ?? ''));
        $dosage = trim((string)($post['dosage'] ?? ''));
        $rx = !empty($post['requires_prescription']);

        $errors = [];
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Nom du médicament invalide (2 à 120 caractères).';
        if (mb_strlen($form) < 2 || mb_strlen($form) > 60) $errors[] = 'Forme galénique requise (ex. Comprimé, Sirop, Spray).';
        if ($errors) return $errors;

        if ($this->service->medicamentExists($name, $dosage !== '' ? $dosage : null, $form, $id)) {
            return ['Ce médicament existe déjà avec le même dosage et la même forme.'];
        }

        $med = new Medicament($id, $name, $dci !== '' ? $dci : null, $form, $dosage !== '' ? $dosage : null, $rx);
        $this->service->saveMedicament($med);
        return [];
    }

    public function supprimerMedicament(int $id): void
    {
        exigerRole(['ADMIN']);
        verifierCsrf();
        $this->service->deleteMedicament($id);
    }

    public function sauverGarde(array $post, int $id = 0): array
    {
        exigerRole(['ADMIN']);
        verifierCsrf();

        $etabId = (int)($post['etablissement_id'] ?? 0);
        $start = trim((string)($post['start_date'] ?? ''));
        $end = trim((string)($post['end_date'] ?? ''));
        $notes = trim((string)($post['notes'] ?? ''));

        $errors = [];
        if ($etabId <= 0) $errors[] = 'Sélectionnez une pharmacie.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $errors[] = 'Dates de garde invalides.';
        if (!$errors && $end < $start) $errors[] = 'La date de fin doit être postérieure ou égale à la date de début.';
        if ($errors) return $errors;

        if ($this->service->gardeOverlaps($etabId, $start, $end, $id)) {
            return ['Cette pharmacie possède déjà une garde qui chevauche cette période.'];
        }

        $garde = new GardePharmacie($id, $etabId, $start, $end, $notes !== '' ? $notes : null);
        $this->service->saveGarde($garde);
        return [];
    }

    public function supprimerGarde(int $id): void
    {
        exigerRole(['ADMIN']);
        verifierCsrf();
        $this->service->deleteGarde($id);
    }

    public function sauverStock(array $post): array
    {
        exigerRole(['ADMIN']);
        verifierCsrf();

        $etabId = (int)($post['etablissement_id'] ?? 0);
        $medId = (int)($post['medicament_id'] ?? 0);
        $qty = (int)($post['quantity'] ?? -1);

        $errors = [];
        if ($etabId <= 0) $errors[] = 'Sélectionnez une pharmacie.';
        if ($medId <= 0) $errors[] = 'Sélectionnez un médicament.';
        if ($qty < 0 || $qty > 10000) $errors[] = 'Quantité en stock invalide (0 à 10 000).';
        if ($errors) return $errors;

        $this->service->setStock($etabId, $medId, $qty);
        return [];
    }

    public function supprimerStock(int $id): void
    {
        exigerRole(['ADMIN']);
        verifierCsrf();
        $this->service->deleteStock($id);
    }
}
