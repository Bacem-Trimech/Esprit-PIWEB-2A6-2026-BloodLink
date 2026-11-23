<?php
/** Entity: a role (table roles). Attributes, constructor, getters and setters only. */
class Role
{
    public function __construct(
        private int $id = 0,
        private string $code = '',
        private string $label = '',
        private string $side = 'front'
    ) {}

    public function getId(): int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getLabel(): string { return $this->label; }
    public function getSide(): string { return $this->side; }

    public function setId(int $id): void { $this->id = $id; }
    public function setCode(string $code): void { $this->code = $code; }
    public function setLabel(string $label): void { $this->label = $label; }
    public function setSide(string $side): void { $this->side = $side; }

    public static function fromArray(array $r): self
    {
        return new self((int)($r['id'] ?? 0), (string)($r['code'] ?? ''), (string)($r['label'] ?? ''), (string)($r['side'] ?? 'front'));
    }
}
