<?php
/**
 * Entity: a user (table users). Attributes, constructor, getters and setters only:
 * no SQL and no business logic here. The role fields come from a JOIN with the roles table.
 */
class User
{
    public function __construct(
        private int $id = 0,
        private string $username = '',
        private string $email = '',
        private string $password = '',
        private int $roleId = 1,
        private string $roleCode = 'donneur',
        private string $roleLabel = 'Donneur',
        private string $roleSide = 'front',
        private string $status = 'active',
        private ?string $city = null,
        private ?string $birthDate = null,
        private string $createdAt = ''
    ) {}

    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRoleId(): int { return $this->roleId; }
    public function getRoleCode(): string { return $this->roleCode; }
    public function getRoleLabel(): string { return $this->roleLabel; }
    public function getRoleSide(): string { return $this->roleSide; }
    public function getStatus(): string { return $this->status; }
    public function getCity(): ?string { return $this->city; }
    public function getBirthDate(): ?string { return $this->birthDate; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setRoleId(int $roleId): void { $this->roleId = $roleId; }
    public function setRoleCode(string $roleCode): void { $this->roleCode = $roleCode; }
    public function setRoleLabel(string $roleLabel): void { $this->roleLabel = $roleLabel; }
    public function setRoleSide(string $roleSide): void { $this->roleSide = $roleSide; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setCity(?string $city): void { $this->city = $city; }
    public function setBirthDate(?string $birthDate): void { $this->birthDate = $birthDate; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }

    /** Builds a User from a SQL row (users JOIN roles). */
    public static function fromArray(array $r): self
    {
        return new self(
            (int)($r['id'] ?? 0), (string)($r['username'] ?? ''), (string)($r['email'] ?? ''), (string)($r['password'] ?? ''),
            (int)($r['role_id'] ?? 1), (string)($r['role_code'] ?? 'donneur'), (string)($r['role_label'] ?? 'Donneur'),
            (string)($r['role_side'] ?? 'front'), (string)($r['status'] ?? 'active'),
            $r['city'] ?? null, $r['birth_date'] ?? null, (string)($r['created_at'] ?? '')
        );
    }
}
