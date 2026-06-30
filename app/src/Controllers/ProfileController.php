<?php

final class ProfileController extends Controller {
    public function index(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        // TODO: render profile page with user data
    }

    public function update(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        // TODO: handle profile update logic
    }
}
