<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class Session extends Model {
    protected static string $name = 'sessions';
    protected static array $schema = [
        'id'            => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id'       => 'INT',
        'session_token' => 'CHAR(64) NOT NULL UNIQUE',
        'csrf_token'    => 'CHAR(64) NOT NULL DEFAULT ""',
        'data'          => 'TEXT NOT NULL',
        'created_at'    => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'expired_at'    => 'TIMESTAMP NOT NULL'
    ];

    /**
     * @var array<string, array{0: string, 1: string, 2: string}> 
     */
    protected static array $relations = [
        'user_id' => ['users', 'id', 'CASCADE']
    ];

    public int $id                = 0;
    public ?int $user_id          = null;
    public ?string $session_token = '';
    public ?string $csrf_token    = '';
    public ?string $data          = '';
    public ?string $created_at    = '';
    public ?string $expired_at    = '';
}
