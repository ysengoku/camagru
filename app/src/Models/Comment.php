<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class Comment extends Model {

    protected static string $name = 'comments';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'post_id' => 'INT',
        'author_id' => 'INT',
        'content' => 'VARCHAR(255) UNIQUE NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    /**
     * @var array<string, array{0: string, 1: string, 2: string}> 
     */
    protected static array $relations = [
        'author_id' => ['users', 'id', 'CASCADE']
    ];

    public int $id = 0;
    public int $post_id = 0;
    public int $author_id = 0;
    public string $content = '';
    public ?string $created_at = '';

    public function __construct(int $potId = 0, int $authorId = 0, string $content = '') {
        $this->post_id = $potId ;
        $this->author_id = $authorId;
        $this->content = $content;
    }

    public static function find(int $commentId): self|null {
        if ($commentId <= 0) {
            return null;
        }

        return self::findById((string) $commentId);
    }

    public static function countByPostId(int $postId): int {
        if ($postId <= 0) {
            return 0;
        }

        return self::count('post_id', $postId);
    }

    public static function findByPostIdWithPagination(int $postId, int $offset = 0, int $limit = 10): array {
        if ($postId <= 0) {
            return [];
        }

        return self::findWithPagination('post_id', $postId, 'created_at', 'DESC', $offset, $limit);
    }

    /**
     * Get the user who wrote the comment.
     */
    public function author(): ?User {
        $relation = static::$relations['author_id'] ?? null;
        if (!$relation || $this->author_id <= 0) {
            return null;
        }
        return User::find($this->author_id);
    }
}
