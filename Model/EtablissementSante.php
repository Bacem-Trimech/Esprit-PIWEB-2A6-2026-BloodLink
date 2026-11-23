<?php
class EtablissementSante {
    public function all(): array { return $_SESSION['etablissements'] ?? []; }
    public function find(int $id): ?array {
        foreach ($this->all() as $item) if ((int)$item['id'] === $id) return $item;
        return null;
    }
    public function create(array $data): void {
        $items = $this->all();
        $data['id'] = empty($items) ? 1 : max(array_column($items, 'id')) + 1;
        $items[] = $data; $_SESSION['etablissements'] = $items;
    }
    public function update(int $id, array $data): bool {
        $items = $this->all();
        foreach ($items as $i => $item) if ((int)$item['id'] === $id) {
            $data['id'] = $id; $items[$i] = $data; $_SESSION['etablissements'] = $items; return true;
        }
        return false;
    }
    public function delete(int $id): bool {
        $items = $this->all();
        $filtered = array_values(array_filter($items, fn($x) => (int)$x['id'] !== $id));
        if (count($filtered) === count($items)) return false;
        $_SESSION['etablissements'] = $filtered; return true;
    }
}
