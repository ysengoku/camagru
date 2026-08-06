<?php

final class PostDataFactory {
    /** @param list<PostCommentData> $comments */
    public static function fromPost(Post $post, ?int $currentUserId, array $comments = []): PostData {
        $author = $post->user();
        $isLiked = $currentUserId !== null && Like::likedByUser($currentUserId, $post->id);

        return new PostData(
            id: $post->id,
            author_name: $author?->username ?? 'Unknown',
            author_avatar: $author?->avatar ?? null,
            image_path: $post->image_path,
            created_at: $post->created_at ?? '',
            likes_count: Like::countByPostId($post->id),
            is_liked_by_current_user: $isLiked,
            comments_count: Comment::countByPostId($post->id),
            comments: $comments,
        );
    }

    public static function toCommentData(Comment $comment): PostCommentData {
        $author = $comment->author();

        return new PostCommentData(
            id: $comment->id,
            author_id: $author?->id ?? 0,
            author_name: $author?->username ?? 'Unknown',
            author_avatar: $author?->avatar ?? null,
            created_at: $comment->created_at ?? '',
            content: $comment->content
        );
    }
}
