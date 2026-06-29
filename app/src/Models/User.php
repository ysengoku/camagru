<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class User extends Model {
    protected static string $name = 'users';
    protected static array $schema = [
        'id'                          => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username'                    => 'VARCHAR(255) UNIQUE NOT NULL',
        'email'                       => 'VARCHAR(255) UNIQUE NOT NULL',
        'password_hash'               => 'VARCHAR(255) NOT NULL',
        'verification_token'          => 'VARCHAR(64) DEFAULT NULL',
        'email_verified'              => 'TINYINT(1) DEFAULT 0 NOT NULL',
        'email_notifications_enabled' => 'TINYINT(1) DEFAULT 1 NOT NULL',
        'created_at'                  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    public int     $id                          = 0;
    public string  $username                    = '';
    public string  $email                       = '';
    public string  $password_hash               = '';
    public string  $verification_token          = '';
    public int     $email_verified              = 0;
    public int     $email_notifications_enabled = 1;
    public ?string $created_at                  = '';

    public function __construct(string $username, string $email, string $passwordHash, string $verificationToken, int $emailVerified = 0, int $emailNotificationsEnabled = 1) {
        $this->username                    = $username;
        $this->email                       = $email;
        $this->password_hash               = $passwordHash;
        $this->verification_token          = $verificationToken;
        $this->email_verified              = (int) $emailVerified;
        $this->email_notifications_enabled = (int) $emailNotificationsEnabled;
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

    public static function findByUsername(string $username): ?self {
        if (empty($username)) {
            return null;
        }

        return self::findOneByField('username', $username);
    }

    public static function findByEmail(string $email): ?self {
        if (empty($email)) {
            return null;
        }

        return self::findOneByField('email', $email);
    }

    public static function findByVerificationToken(string $token): ?self {
        if (empty($token)) {
            return null;
        }

        return self::findOneByField('verification_token', $token);
    }

    public function createNewUser(): bool {
        return $this->save();
    }
}
