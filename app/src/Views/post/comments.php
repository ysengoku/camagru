<?php

require_once __DIR__ . '/../components/avatar.php';

?>

<?php

if (!function_exists('render_comments')) {
    function render_comments(array $comments): string {
        $commentsHtml = '';
        foreach ($comments as $comment) {
            $avatarHtml = render_avatar($comment->author_name, 'medium', $comment->author_avatar);
            $authorName = htmlspecialchars($comment->author_name);
            $timestamp = strtotime($comment->created_at);
            $createdAt = $timestamp !== false
                ? htmlspecialchars(date('F j, Y, g:i A', $timestamp), ENT_QUOTES)
                : htmlspecialchars($comment->created_at, ENT_QUOTES);
            $content = htmlspecialchars($comment->content);

            $commentsHtml .= <<<HTML
            <div class="flex-col gap-1 mb-4 p-2">
                <div class="flex align-center mb-1 gap-2">
                    {$avatarHtml}
                    <div class="flex align-end gap-2">
                        <span class="color-gray-600 font-bold font-size-4">     
                            {$authorName}
                        </span>
                        <span class="color-gray-500 font-size-3">
                            {$createdAt}
                        </span>
                    </div>
                </div>
                <span class="ps-2">
                    {$content}
                </span>
            </div>
            HTML;
        }

        return <<<HTML
            <div class="post-comments">
                {$commentsHtml}
            </div>
        HTML;
    }
}
