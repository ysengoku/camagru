<?php
if (!function_exists('render_comment_form')) {
    function render_comment_form(int $postId): string {
        return <<<HTML
            <form class="comment-form" data-post-id="{$postId}">
                <textarea name="content" placeholder="Write a comment..." required></textarea>
                <button type="submit" class="button-no-border">
                    Send
                </button>
            </form>
        HTML;
    }
}
