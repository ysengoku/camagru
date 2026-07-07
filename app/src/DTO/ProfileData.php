<?php

final readonly class ProfileData {
    public function __construct(
        public string $username,
        public string $email,
        public ?string $password = null,
        public ?string $newPassword = null,
        public ?string $avatar = null,
        public bool $notificationsEnabled = false
    ) {}
}
