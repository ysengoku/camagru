<?php

final readonly class PostCommentData {
    public function __construct(
        public ?int    $id,
        public int     $author_id,
        public string  $author_name,
        public ?string $author_avatar,
        public string  $created_at,
        public string  $content,
    ) {}
}
