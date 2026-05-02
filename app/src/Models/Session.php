<?php

class Session extends Model
{
    protected static string $name = 'sessions';
    protected static array $schema = [
        'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id'    => 'INT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];
    protected static array $relations = [
        'user_id' => ['users', 'id', 'CASCADE']
    ];

    public int $id = 0;
    public int $user_id = 0;
    public ?string $created_at = '';
}
