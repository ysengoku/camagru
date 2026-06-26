<?php
    require_once __DIR__ . '/../components/icon.php';
?>

<?php
if (!function_exists('render_studio_menu')) {
    function render_studio_menu(): string {
        $webcamIcon = render_icon('webcam');
        $uploadIcon = render_icon('upload');

        return <<<HTML
        <div id="studio-menu" class="flex align-center justify-center gap-4  m-4">
            <button
                id="webcam-button"
                class="studio-menu-button flex-col align-center border-none bg-transparent pointer-cursor m-4"
                data-action="webcam"
            >
                {$webcamIcon}
                <span>Activate Webcam</span>
            </button>
            <label
                for="upload-input"
                class="studio-menu-button flex-col align-center border-none bg-transparent pointer-cursor m-4"
            >
                {$uploadIcon}
                <input
                    type="file"
                    id="upload-input"
                    class="display-none"
                    accept="image/png, image/jpeg"
                />
                <span>Upload Image</span>
          </label>
        </div>
        HTML;
    }
}
?>
