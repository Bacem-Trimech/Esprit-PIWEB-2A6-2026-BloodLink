<?php
declare(strict_types=1);

final class Utilisateur
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $email,
        private string $role,
        private bool $actif
    ) {}
    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function estActif(): bool { return $this->actif; }
}
