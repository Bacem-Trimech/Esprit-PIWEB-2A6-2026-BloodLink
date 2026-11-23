<?php
declare(strict_types=1);

final class AlerteSos
{
    public function __construct(
        private ?int $id = null,
        private string $lieu = '',
        private string $ville = '',
        private string $groupeReceveur = '',
        private string $telephone = '',
        private ?string $message = null,
        private string $ipHash = '',
        private ?string $creeLe = null,
        private ?string $expireLe = null,
        private ?string $supprimeLe = null
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            (string)($row['lieu'] ?? ''),
            (string)($row['ville'] ?? ''),
            (string)($row['groupe_receveur'] ?? ''),
            (string)($row['telephone'] ?? ''),
            isset($row['message']) && $row['message'] !== '' ? (string)$row['message'] : null,
            (string)($row['ip_hash'] ?? ''),
            isset($row['cree_le']) ? (string)$row['cree_le'] : null,
            isset($row['expire_le']) ? (string)$row['expire_le'] : null,
            isset($row['supprime_le']) ? (string)$row['supprime_le'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'lieu' => $this->lieu,
            'ville' => $this->ville,
            'groupe_receveur' => $this->groupeReceveur,
            'telephone' => $this->telephone,
            'message' => $this->message,
            'cree_le' => $this->creeLe,
            'expire_le' => $this->expireLe,
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function getLieu(): string { return $this->lieu; }
    public function getVille(): string { return $this->ville; }
    public function getGroupeReceveur(): string { return $this->groupeReceveur; }
    public function getTelephone(): string { return $this->telephone; }
    public function getMessage(): ?string { return $this->message; }
    public function getIpHash(): string { return $this->ipHash; }
    public function getCreeLe(): ?string { return $this->creeLe; }
    public function getExpireLe(): ?string { return $this->expireLe; }
    public function getSupprimeLe(): ?string { return $this->supprimeLe; }
}
