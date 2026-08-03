<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class StudioConfigController extends Controller {
    /**
     * Returns studio configuration used by the camera/studio UI: max sticker
     * count, available text fonts/sizes, and filter presets.
     *
     * @route GET /api/studio-config
     * @response 200 {maxStickerCount, text, filters} Studio configuration object
     */
    final public function config(): string {
        $config = require __DIR__ . '/../../config/studio.php';
        
        header('Content-Type: application/json');
        echo json_encode($config);
        exit;
    }
}
