<?php

final class ServiceResult {
    public function __construct(
        public readonly bool $success,
        public readonly array $errors = [],
        public readonly mixed $data = null
    ) {}

    public static function success(mixed $data = null): self {
        return new self(true, [], $data);
    }

    public static function failure(array $errors = []): self {
        return new self(false, $errors);
    }
}
