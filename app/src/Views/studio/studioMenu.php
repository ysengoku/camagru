<div id="studio-menu" class="flex align-center justify-center gap-4  m-4">
    <button
        class="studio-menu-button flex-col align-center border-none bg-transparent pointer-cursor m-4"
        data-action="webcam"
    >
        <?php $name = 'webcam'; include __DIR__ . '/../components/icon.php'; ?>
        <span class="text-sm">Activate Webcam</span>
    </button>
    <input type="file" id="upload-input" class="display-none" accept="image/*" />
    <button
        class="studio-menu-button flex-col align-center border-none bg-transparent pointer-cursor m-4"
        data-action="upload"
    >
        <?php $name = 'upload'; include __DIR__ . '/../components/icon.php'; ?>
        <span class="text-sm">Upload Image</span>
  </button>
</div>
