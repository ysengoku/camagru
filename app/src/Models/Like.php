<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class Like extends Model {
    protected static string $name = 'likes';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'post_id' => 'INT',
        'author_id' => 'INT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    /**
     * @var array<string, array{0: string, 1: string, 2: string}> 
     */
    protected static array $relations = [
        'post_id' => ['posts', 'id', 'CASCADE'],
        'author_id' => ['users', 'id', 'CASCADE'],
    ];

    public int $id = 0;
    public int $post_id = 0;
    public int $author_id = 0;
    public ?string $created_at = '';

    public function __construct() {
    }

    public function find(int $id): ?self {
        if ($id <= 0) {
            return null;
        }

        return self::findById((string) $id);
    }

    public static function countByPostId(int $postId): int {
        if ($postId <= 0) {
            return 0;
        }

        $db = Database::getInstance();
        $sql = 'SELECT COUNT(*) as like_count FROM likes WHERE post_id = :post_id';
        $result = $db->fetch($sql, ['post_id' => $postId]);

        return (int) ($result['like_count'] ?? 0);
    }
}
