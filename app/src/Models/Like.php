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

    public function __construct(int $author_id = 0, int $post_id = 0) {
        $this->author_id = $author_id;
        $this->post_id = $post_id;
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

        return self::countByField('post_id', $postId);
    }

    public static function likedByUser(int $userId, int $postId): bool {
        if ($userId <= 0 || $postId <= 0) {
            return false;
        }

        $db = Database::getInstance();
        $sql = 'SELECT COUNT(*) as count FROM likes WHERE author_id = :author_id AND post_id = :post_id';
        $result = $db->fetch($sql, ['author_id' => $userId, 'post_id' => $postId]);

        return $result !== null && (int) $result['count'] > 0;
    }

    public static function findByUserAndPost(int $userId, int $postId): ?self {
        if ($userId <= 0 || $postId <= 0) {
            return null;
        }

        $db = Database::getInstance();
        $sql = 'SELECT * FROM likes WHERE author_id = :author_id AND post_id = :post_id LIMIT 1';
        $result = $db->fetch($sql, ['author_id' => $userId, 'post_id' => $postId]);

        if ($result === false || $result === null) {
            return null;
        }

        return self::fromRow($result);
    }
}
