<?php
/**
 * UserService: ALL the SQL of the users module (prepared statements only).
 */
class UserService
{
    private PDO $db;
    private const SELECT = 'SELECT u.*, r.code AS role_code, r.label AS role_label, r.side AS role_side
                            FROM users u JOIN roles r ON r.id = u.role_id';

    public function __construct()
    {
        $this->db = Database::getConnexion();
    }

    private function one(string $where, array $params): ?User
    {
        $stmt = $this->db->prepare(self::SELECT . " WHERE $where LIMIT 1");
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function findById(int $id): ?User { return $this->one('u.id = :id', ['id' => $id]); }
    public function findByEmail(string $email): ?User { return $this->one('u.email = :email', ['email' => $email]); }

    /** @return User[] */
    public function findAll(string $q = '', ?string $roleCode = null): array
    {
        $sql = self::SELECT . ' WHERE 1 = 1';
        $p = [];
        if ($q !== '') {
            $sql .= ' AND (u.username LIKE :q1 OR u.email LIKE :q2)';
            $p['q1'] = $p['q2'] = '%' . $q . '%';
        }
        if ($roleCode) {
            $sql .= ' AND r.code = :role';
            $p['role'] = $roleCode;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY u.id ASC');
        $stmt->execute($p);
        return array_map(fn(array $r) => User::fromArray($r), $stmt->fetchAll());
    }

    /** @return User[] */
    public function recent(int $n = 5): array
    {
        $stmt = $this->db->prepare(self::SELECT . ' ORDER BY u.id DESC LIMIT :n');
        $stmt->bindValue(':n', $n, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn(array $r) => User::fromArray($r), $stmt->fetchAll());
    }

    /** True if the email or username is already used by another user. */
    public function exists(string $email, string $username, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE (email = :email OR username = :username) AND id <> :id LIMIT 1');
        $stmt->execute(['email' => $email, 'username' => $username, 'id' => $exceptId]);
        return (bool)$stmt->fetch();
    }

    public function create(User $u): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, role_id, status, city, birth_date)
             VALUES (:username, :email, :password, :role, :status, :city, :birth)'
        );
        $stmt->execute([
            'username' => $u->getUsername(), 'email' => $u->getEmail(), 'password' => $u->getPassword(),
            'role' => $u->getRoleId(), 'status' => $u->getStatus(), 'city' => $u->getCity(), 'birth' => $u->getBirthDate(),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(User $u): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET username = :username, email = :email, role_id = :role, city = :city, birth_date = :birth WHERE id = :id'
        );
        return $stmt->execute([
            'username' => $u->getUsername(), 'email' => $u->getEmail(), 'role' => $u->getRoleId(),
            'city' => $u->getCity(), 'birth' => $u->getBirthDate(), 'id' => $u->getId(),
        ]);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        return $this->db->prepare('UPDATE users SET password = :p WHERE id = :id')->execute(['p' => $hash, 'id' => $id]);
    }

    public function setStatus(int $id, string $status): bool
    {
        return $this->db->prepare('UPDATE users SET status = :s WHERE id = :id')->execute(['s' => $status, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
    }

    public function countAll(): int { return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(); }

    public function countByRole(string $code): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.code = :c');
        $stmt->execute(['c' => $code]);
        return (int)$stmt->fetchColumn();
    }

    /** @return array<string,int> role code => number of users (JOIN + GROUP BY) */
    public function roleCounts(): array
    {
        return $this->db->query('SELECT r.code, COUNT(u.id) FROM roles r LEFT JOIN users u ON u.role_id = r.id GROUP BY r.id, r.code ORDER BY r.id')
                        ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /** @return Role[] */
    public function roles(): array
    {
        return array_map(fn(array $r) => Role::fromArray($r), $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll());
    }

    public function roleIdByCode(string $code): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE code = :c');
        $stmt->execute(['c' => $code]);
        return (int)$stmt->fetchColumn();
    }
}
