<?php

final class PhotoApiControllerTest extends DbTestCase {
    public function testTodo(): void {
        $this->markTestIncomplete('create()/delete()/getPhotos()/getCurrentUserPhotos() tests not written yet.');
    }
}

// - [ ] create(): missing baseImage/stickers → 422; success → 201 with html
// - [ ] delete(): invalid ID → 400; not found → 404; not your post → 403; success → 200
// - [ ] getPhotos() / getCurrentUserPhotos(): offset/limit validation, pagination math at boundaries (e.g. limit clamped when fewer posts remain than requested)