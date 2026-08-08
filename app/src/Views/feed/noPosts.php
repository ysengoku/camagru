<?php

if (!function_exists('render_no_posts')) {
    function render_no_posts(): string {
        return <<<HTML
        <div class="flex justify-center align-center flex-1 gap-4 m-4 h-100">
            <div class="flex-col justify-center gap-4 mx-4">
                <h1 class="noposts">Nothing here yet</h1>
                <p class="color-gray-500 font-size-5">Be the first to share a moment!.</p>
            </div>
            <img src="/assets/img/selfy-rabbit.png" alt="Selfy rabbit" class="noposts-img" />
        </div>
        HTML;
    }
}
