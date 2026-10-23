<?php

declare(strict_types=1);

namespace EczaneApi\DTO;

use JsonSerializable;

class ApiResponse implements JsonSerializable
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null,
        public readonly array $meta = [],
        public readonly int $statusCode = 200
    ) {}

    public static function success(mixed $data = null, string $message = 'İşlem başarılı', array $meta = [], int $status = 200): self
    {
        return new self(
            success: true,
            message: $message,
            data: $data,
            meta: array_merge([
                'timestamp' => date('c'),
                'count' => is_countable($data) ? count($data) : (is_array($data) ? count($data) : null),
            ], $meta),
            statusCode: $status
        );
    }

    public static function error(string $message = 'Bir hata oluştu', int $status = 400, mixed $data = null, array $meta = []): self
    {
        return new self(
            success: false,
            message: $message,
            data: $data,
            meta: array_merge([
                'timestamp' => date('c'),
            ], $meta),
            statusCode: $status
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'meta' => $this->meta,
            'data' => $this->data,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
