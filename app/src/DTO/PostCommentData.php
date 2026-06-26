<?php

final readonly class PostCommentData {
    public function __construct(
        public string $author_name,
        public string $text,
    ) {}
}
