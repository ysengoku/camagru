<?php

final class ProfileController extends Controller {
    public function index(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        return $this->render([
            'pageScript' => 'profile',
            'pageTitle' => 'Profile']);
    }

    public function update(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        // TODO: handle profile update logic
        return $this->json(['message' => 'Profile updated successfully'], Response::OK);
    }
}
