<?php

require_once __DIR__.'/../Core/Model.php';

class Comment extends Model {
    protected static string $name = 'comments';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'post_id' => 'INT',
        'author_id' => 'INT',
        'content' => 'VARCHAR(255) UNIQUE NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    public int $id = 0;
    public int $post_id = 0;
    public int $author_id = 0;
    public string $content = '';
    public ?string $created_at = '';

    public function __construct() {
    }

}