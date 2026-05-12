<div id="studio-canvas" class="studio-section">
    <div class="flex align-center justify-center">
        <?php include __DIR__ . '/overlayList.php'; ?>

    </div>

    <?php include __DIR__ . '/studioMenu.php'; ?>
    <?php include __DIR__ . '/studioEditor.php'; ?>

    <div class="studio-buttons flex justify-center my-4 gap-2">
        <button
          type="button"
          onclick="studio.capturePhoto()"
          id="capture-button"
          disabled
        >
            Capture
        </button>
        <button
          type="button"
          onclick="studio.openUploadModal()"
          id="upload-button"
        >
            Upload Photo
        </button>

        <button
          type="button"
          onclick="studio.sharePhoto()"
          id="share-button"
          class="display-none"
        >
            Share
        </button>

        <button
          type="button"
          onclick="studio.resetStudio()"
          id="reset-button"
          class="display-none"
        >
            Reset
        </button>
    </div>
</div>
