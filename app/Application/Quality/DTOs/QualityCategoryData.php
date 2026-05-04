<?php

namespace App\Application\Quality\DTOs;

class QualityCategoryData
{
    /** @var int|null */
    public $id;

    /** @var string */
    public $name;

    public function __construct(?int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name']
        );
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
