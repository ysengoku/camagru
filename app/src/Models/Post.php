<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class Post extends Model {
    protected static string $name  = 'posts';
    protected static array $schema = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT NOT NULL',
        'image_path' => 'VARCHAR(255) UNIQUE NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    /**
     * @var array<string, array{0: string, 1: string, 2: string}> 
     */
    protected static array $relations = [
        'user_id' => ['users', 'id', 'CASCADE']
    ];

    public int $id = 0;
    public int $user_id = 0;
    public string $image_path = '';
    public ?string $created_at = null;

    public function __construct(string $image_path = '', int $user_id = 0) {
        $this->image_path = $image_path;
        $this->user_id = $user_id;
    }

    public static function find(int $id): ?self {
        if ($id <= 0) {
            return null;
        }

        return self::findById((string) $id);
    }

    /**
     * Find posts with pagination.
     * @param int $offset
     * @param int $limit
     * @return list<self>
     */
    public static function findAllUsersPostsWithPagination(int $offset = 0, int $limit = 10): array {
        return self::findWithPagination(null, null, 'created_at', 'DESC', $offset, $limit);
    }

    /**
     * @return list<self>
     */
    public static function findByUserId(int $userId): array {
        if ($userId <= 0) {
            return [];
        }

        $db = self::getDb();
        $sql = 'SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC';

        /** @var list<array<string, mixed>> $rows */
        $rows = $db->fetchAll($sql, ['user_id' => $userId]);

        return array_map(fn(array $row): self => static::fromRow($row), $rows);
    }

    public static function findAllWithPagination(int $offset = 0, int $limit = 10): array {
        return self::findWithPagination(null, null, 'created_at', 'DESC', $offset, $limit);
    }

    public static function findByUserIdWithPagination(int $userId, int $offset = 0, int $limit = 10): array {
        if ($userId <= 0) {
            return [];
        }

        return self::findWithPagination('user_id', $userId, 'created_at', 'DESC', $offset, $limit);
    }

    /**
     * @return self|null
     */
    public static function findByPath(string $imagePath): ?self {
        if (trim($imagePath) === '') {
            return null;
        }

        $db = self::getDb();
        $sql = 'SELECT * FROM posts WHERE image_path = :image_path LIMIT 1';

        /** @var array<string, mixed>|null $row */
        $row = $db->fetch($sql, ['image_path' => $imagePath]);

        return $row === null ? null : static::fromRow($row);
    }

    public static function countAll(): int {
        return self::count(null, null);
    }

    #[Override]
    protected function beforeSave(): bool {
        $this->image_path = trim($this->image_path);

        return true;
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): ?User {
        $relation = static::$relations['user_id'] ?? null;
        if (!$relation || $this->user_id <= 0) {
            return null;
        }
        return User::find($this->user_id);
    }

    /**
     * Validate the post data before saving.
     * @return bool
     */
    #[Override]
    public function validate(): bool {
        /** @var list<string> $errors */
        $this->errors = [];

        if ($this->user_id <= 0) {
            $this->errors[] = 'User is required.';
        } elseif (!self::userExists($this->user_id)) {
            $this->errors[] = 'User does not exist.';
        }

        if (trim($this->image_path) === '') {
            $this->errors[] = 'Image path is required.';
        } elseif (strlen($this->image_path) > 255) {
            $this->errors[] = 'Image path must be 255 characters or fewer.';
        } elseif (strncmp($this->image_path, '/media/', 7) !== 0) {
            $this->errors[] = 'Image path must point to uploaded media.';
        }

        return empty($this->errors);
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'image_path' => $this->image_path,
            'created_at' => $this->created_at,
        ];
    }

    private static function userExists(int $userId): bool {
        $db = self::getDb();
        $user = $db->fetch(
            'SELECT id FROM users WHERE id = :id LIMIT 1',
            ['id' => $userId]
        );

        return $user !== null;
    }
}
