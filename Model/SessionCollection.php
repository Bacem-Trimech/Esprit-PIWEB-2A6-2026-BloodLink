<?php
/** Stockage temporaire en session PHP, sans SQL ni JSON. */
class SessionCollection {
    public function all(string $key): array { return $_SESSION[$key] ?? []; }
    public function find(string $key, int $id): ?array {
        foreach ($this->all($key) as $row) if ((int)($row['id'] ?? 0) === $id) return $row;
        return null;
    }
    public function create(string $key, array $data): int {
        $rows = $this->all($key); $id = $rows ? max(array_column($rows, 'id')) + 1 : 1;
        $data['id'] = $id; $rows[] = $data; $_SESSION[$key] = $rows; return $id;
    }
    public function update(string $key, int $id, array $data): bool {
        $rows = $this->all($key);
        foreach ($rows as $i => $row) if ((int)$row['id'] === $id) { $data['id']=$id; $rows[$i]=$data; $_SESSION[$key]=$rows; return true; }
        return false;
    }
    public function delete(string $key, int $id): bool {
        $rows=$this->all($key); $filtered=array_values(array_filter($rows, fn($r)=>(int)$r['id']!==$id));
        if (count($rows)===count($filtered)) return false; $_SESSION[$key]=$filtered; return true;
    }
}
