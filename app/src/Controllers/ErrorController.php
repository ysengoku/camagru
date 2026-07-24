<?php

final class ErrorController extends Controller {
    /** @psalm-suppress PossiblyUnusedMethod - Called dynamically via Controller::run() from Application::renderNotFound() */
    public function notFound(): string {
        return $this->render(['pageTitle' => 'Not Found'], '404');
    }
}
