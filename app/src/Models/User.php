<?php

require_once __DIR__.'/../Core/Model.php';

class User extends Model {
    protected static string $name = 'users';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(255) UNIQUE NOT NULL',
        'email' => 'VARCHAR(255) UNIQUE NOT NULL',
        'password_hash' => 'VARCHAR(255) NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    public int $id = 0;
    public string $username = '';
    public string $email = '';
    public string $password_hash = '';
    public ?string $created_at = '';

    public function __construct() {
    }

}
