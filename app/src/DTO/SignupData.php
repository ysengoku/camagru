<?php

final readonly class SignupData {
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
    ) {}
}
