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

        return self::findById($commentId);
    }

    public static function countByPostId(int $postId): int {
        if ($postId <= 0) {
            return 0;
        }

        return self::countByField('post_id', $postId);
    }

    public static function findByPostIdWithPagination(int $postId, int $offset = 0, int $limit = 5): array {
        if ($postId <= 0) {
            return [];
        }

        return self::findByFieldWithPagination('post_id', $postId, $offset, $limit);
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'author_id' => $this->author_id,
            'content' => $this->content,
            'created_at' => $this->created_at,
        ];
    }
}
