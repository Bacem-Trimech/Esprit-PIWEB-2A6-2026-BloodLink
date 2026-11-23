<?php
/** Pharmacies module: public search pages (front office) and CRUD of medicines, duty shifts and stock (back office). */
class PharmacieController extends BaseController
{
    private const BAD_TOKEN = 'Invalid security token. Please try again.';
    private const FORMS = ['Comprimé', 'Gélule', 'Sirop', 'Sachet', 'Spray', 'Injection', 'Pommade', 'Autre'];

    public function __construct(AuthService $auth, UserService $users, private PharmacieService $ph)
    {
        parent::__construct($auth, $users);
    }

    // ================================================================ FRONT OFFICE (public)
    public function pharmacies(): void
    {
        $q = trim($_GET['q'] ?? '');
        $city = trim($_GET['city'] ?? '');
        $onDuty = isset($_GET['searched']) ? isset($_GET['duty']) : true;     // "on duty" is ticked by default
        $results = $q !== '' ? $this->ph->searchStock($q, $city, $onDuty) : $this->ph->listPharmacies($city, $onDuty);
        $this->render('pharmacies', ['title' => 'Pharmacies', 'q' => $q, 'city' => $city, 'onDuty' => $onDuty,
                                     'results' => $results, 'cities' => $this->ph->cities()]);
    }

    public function medicaments(): void
    {
        $q = trim($_GET['q'] ?? '');
        $this->render('medicaments', ['title' => 'Medicines', 'q' => $q, 'list' => $this->ph->medicaments($q)]);
    }

    // ================================================================ BACK OFFICE: medicines
    public function medList(): void
    {
        $this->need('pharmacy.view');
        $q = trim($_GET['q'] ?? '');
        $this->render('meds', ['title' => 'Medicines', 'q' => $q, 'list' => $this->ph->medicaments($q)], 'back');
    }

    public function medCreate(): void { $this->medForm(null); }
    public function medEdit(): void { $this->medForm((int)($_GET['id'] ?? 0)); }

    private function medForm(?int $id): void
    {
        $this->need('pharmacy.manage');
        $m = $id === null ? new Medicament() : $this->ph->findMedicament($id);
        if (!$m) $this->missing('back');
        $errors = [];
        $old = ['name' => $m->getName(), 'dci' => (string)$m->getDci(), 'form' => $m->getForm(), 'dosage' => (string)$m->getDosage(), 'rx' => $m->getRequiresPrescription()];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = ['name' => trim($_POST['name'] ?? ''), 'dci' => trim($_POST['dci'] ?? ''), 'form' => trim($_POST['form'] ?? ''),
                    'dosage' => trim($_POST['dosage'] ?? ''), 'rx' => isset($_POST['requires_prescription'])];
            if (!$this->auth->verifyCsrf()) {
                $errors[] = self::BAD_TOKEN;
            } else {
                $errors = array_values(array_filter([
                    Validator::text($old['name'], 2, 120, 'Name'), Validator::text($old['dci'], 0, 120, 'Active substance'),
                    Validator::text($old['dosage'], 0, 40, 'Dosage'),
                    in_array($old['form'], self::FORMS, true) ? null : 'Choose a valid form.',
                ]));
                if (!$errors && $this->ph->medicamentExists($old['name'], $old['dosage'] ?: null, $old['form'], $m->getId())) {
                    $errors[] = 'This medicine already exists with the same dosage and form.';
                }
                if (!$errors) {
                    $m->setName($old['name']); $m->setDci($old['dci'] ?: null); $m->setForm($old['form']);
                    $m->setDosage($old['dosage'] ?: null); $m->setRequiresPrescription($old['rx']);
                    $this->ph->saveMedicament($m);
                    $this->flash('ok', $id === null ? 'Medicine added.' : 'Medicine updated.');
                    $this->redirect('med_list');
                }
            }
        }
        $this->render('med_form', ['title' => $id === null ? 'Add medicine' : 'Edit medicine', 'isNew' => $id === null,
                                   'old' => $old, 'errors' => $errors, 'forms' => self::FORMS], 'back');
    }

    public function medDelete(): void
    {
        $this->need('pharmacy.manage');
        $this->requirePost('med_list');
        $this->ph->deleteMedicament((int)($_POST['id'] ?? 0));
        $this->flash('ok', 'Medicine deleted (its stock lines too).');
        $this->redirect('med_list');
    }

    // ================================================================ BACK OFFICE: duty shifts (gardes)
    public function gardeList(): void
    {
        $this->need('pharmacy.view');
        $this->render('gardes', ['title' => 'Duty shifts', 'list' => $this->ph->gardes(), 'today' => date('Y-m-d')], 'back');
    }

    public function gardeCreate(): void { $this->gardeForm(null); }
    public function gardeEdit(): void { $this->gardeForm((int)($_GET['id'] ?? 0)); }

    private function gardeForm(?int $id): void
    {
        $this->need('pharmacy.manage');
        $g = $id === null ? new GardePharmacie() : $this->ph->findGarde($id);
        if (!$g) $this->missing('back');
        $pharmacies = $this->ph->pharmacies();
        $ids = array_map(fn(EtablissementSante $p) => $p->getId(), $pharmacies);
        $errors = [];
        $old = ['etab' => $g->getEtablissementId() ?: ($ids[0] ?? 0), 'start' => $g->getStartDate() ?: date('Y-m-d'),
                'end' => $g->getEndDate() ?: date('Y-m-d', strtotime('+6 days')), 'notes' => (string)$g->getNotes()];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = ['etab' => (int)($_POST['etablissement_id'] ?? 0), 'start' => trim($_POST['start_date'] ?? ''),
                    'end' => trim($_POST['end_date'] ?? ''), 'notes' => trim($_POST['notes'] ?? '')];
            if (!$this->auth->verifyCsrf()) {
                $errors[] = self::BAD_TOKEN;
            } else {
                $errors = array_values(array_filter([
                    in_array($old['etab'], $ids, true) ? null : 'Choose a pharmacy.',
                    Validator::dateRange($old['start'], $old['end']), Validator::text($old['notes'], 0, 200, 'Notes'),
                ]));
                if (!$errors && $this->ph->gardeOverlaps($old['etab'], $old['start'], $old['end'], $g->getId())) {
                    $errors[] = 'This pharmacy already has a duty shift in this period.';
                }
                if (!$errors) {
                    $g->setEtablissementId($old['etab']); $g->setStartDate($old['start']); $g->setEndDate($old['end']);
                    $g->setNotes($old['notes'] ?: null);
                    $this->ph->saveGarde($g);
                    $this->flash('ok', $id === null ? 'Duty shift added.' : 'Duty shift updated.');
                    $this->redirect('garde_list');
                }
            }
        }
        $this->render('garde_form', ['title' => $id === null ? 'Add duty shift' : 'Edit duty shift', 'old' => $old,
                                     'errors' => $errors, 'pharmacies' => $pharmacies, 'isNew' => $id === null], 'back');
    }

    public function gardeDelete(): void
    {
        $this->need('pharmacy.manage');
        $this->requirePost('garde_list');
        $this->ph->deleteGarde((int)($_POST['id'] ?? 0));
        $this->flash('ok', 'Duty shift deleted.');
        $this->redirect('garde_list');
    }

    // ================================================================ BACK OFFICE: stock (disponibilites)
    public function stockList(): void
    {
        $this->need('pharmacy.view');
        $this->render('stock', ['title' => 'Stock', 'list' => $this->ph->disponibilites(), 'pharmacies' => $this->ph->pharmacies(),
                                'meds' => $this->ph->medicaments(), 'canManage' => $this->auth->can('pharmacy.manage')], 'back');
    }

    public function stockSave(): void
    {
        $this->need('pharmacy.manage');
        $this->requirePost('stock_list');
        $etab = (int)($_POST['etablissement_id'] ?? 0);
        $med = (int)($_POST['medicament_id'] ?? 0);
        $qty = trim($_POST['quantity'] ?? '');
        $ids = array_map(fn(EtablissementSante $p) => $p->getId(), $this->ph->pharmacies());
        if (!in_array($etab, $ids, true) || !$this->ph->findMedicament($med)) {
            $this->flash('error', 'Unknown pharmacy or medicine.');
        } elseif ($err = Validator::quantity($qty)) {
            $this->flash('error', $err);
        } else {
            $this->ph->setStock($etab, $med, (int)$qty);
            $this->flash('ok', 'Stock saved.');
        }
        $this->redirect('stock_list');
    }

    public function stockDelete(): void
    {
        $this->need('pharmacy.manage');
        $this->requirePost('stock_list');
        $this->ph->deleteStock((int)($_POST['id'] ?? 0));
        $this->flash('ok', 'Stock line deleted.');
        $this->redirect('stock_list');
    }

    /** Destructive actions: POST + CSRF token only. */
    private function requirePost(string $back): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->auth->verifyCsrf()) {
            $this->flash('error', self::BAD_TOKEN);
            $this->redirect($back);
        }
    }
}
