<?php

class Post extends Model {
    protected static string $name  = 'posts';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT',
        'url' => 'VARCHAR(255) UNIQUE NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    public int $id = 0;
    public int $user_id = 0;
    public string $url = '';
    public ?string $created_at = '';

    public function __construct() {
    }
}
