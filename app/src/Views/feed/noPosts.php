<?php

if (!function_exists('render_no_posts')) {
    function render_no_posts(): string {
        return <<<HTML
        <div class="no-posts flex justify-center align-center flex-1 gap-4 mx-4">
            <div class="flex-col align-center gap-2 me-4">
                <span class="noposts">Nothing here yet</span>
                <p class="color-gray-500">Be the first to share a moment!</p>
            </div>
            <img src="/assets/img/selfy-rabbit.png" alt="Selfy rabbit" class="ms-4" />

        </div>
        HTML;
    }
}
