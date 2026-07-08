<?php
if (!function_exists('render_comment_form')) {
    function render_comment_form(int $postId): string {
        return <<<HTML
            <form action="/post/{$postId}/comment" method="POST" class="comment-form">
                <textarea name="content" placeholder="Write a comment..." required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        HTML;
    }
}
