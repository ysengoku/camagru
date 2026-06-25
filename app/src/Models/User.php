<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class User extends Model {
    protected static string $name = 'users';
    protected static array $schema = [
        'id'                 => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username'           => 'VARCHAR(255) UNIQUE NOT NULL',
        'email'              => 'VARCHAR(255) UNIQUE NOT NULL',
        'password_hash'      => 'VARCHAR(255) NOT NULL',
        'email_verified'     => 'TINYINT(1) DEFAULT 0 NOT NULL',
        'verification_token' => 'VARCHAR(64) DEFAULT NULL',
        'created_at'         => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    public int $id                    = 0;
    public string $username           = '';
    public string $email              = '';
    public string $password_hash      = '';
    public bool $email_verified       = false;
    public string $verification_token = '';
    public ?string $created_at        = '';

    public function __construct() {
        
    }

    public static function getCurrentUser(): ?self {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userId = $_SESSION['user_id'];

        return self::findById($userId);
    }

    public static function find(int $id): ?self {
        if ($id <= 0) {
            return null;
        }

        return self::findById((string) $id);
    }
}
