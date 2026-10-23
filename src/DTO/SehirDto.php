<?php

declare(strict_types=1);

namespace EczaneApi\DTO;

use JsonSerializable;

class SehirDto implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly array $districts = []
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'districts_count' => count($this->districts),
            'districts' => $this->districts,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
