<?php

final readonly class PostData {
    public function __construct(
        public int $id,
        public string $author_name,
        public int $author_id,
        public ?string $author_avatar,
        public string $image_path,
        public string $created_at,
        public int $likes_count,
        public bool $is_liked_by_current_user,
        public int $comments_count,
        /** @var list<PostCommentData> */
        public array $comments,
    ) {}
}
