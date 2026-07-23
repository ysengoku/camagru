<?php

final class ErrorController extends Controller {
    public function notFound(): string {
        return $this->render(['pageTitle' => 'Not Found'], '404');
    }
}
