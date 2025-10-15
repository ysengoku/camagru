<?php

require_once __DIR__.'/../Core/Model.php';

class Like extends Model {
    protected static string $name = 'likes';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'post_id' => 'INT',
        'author_id' => 'INT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    public int $id = 0;
    public int $post_id = 0;
    public int $author_id = 0;
    public ?string $created_at = '';

    public function __construct() {
    }

}