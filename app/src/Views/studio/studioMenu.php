<div id="studio-menu" class="flex align-center justify-center gap-4  m-4">
    <button
        id="webcam-button"
        class="studio-menu-button flex-col align-center border-none bg-transparent pointer-cursor m-4"
        data-action="webcam"
    >
        <?php $name = 'webcam'; include __DIR__ . '/../components/icon.php'; ?>
        <span class="text-sm">Activate Webcam</span>
    </button>
    <label
        for="upload-input"
        class="studio-menu-button flex-col align-center border-none bg-transparent pointer-cursor m-4"
    >
        <?php $name = 'upload'; include __DIR__ . '/../components/icon.php'; ?>
        <input
            type="file"
            id="upload-input"
            class="display-none"
            accept="image/png, image/jpeg"
        />
        <span class="text-sm">Upload Image</span>
  </label>
</div>
