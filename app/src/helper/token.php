<?php

function generateToken(int $length = 32, int $expiresInMinutes = 60): array {
    $token = bin2hex(random_bytes($length));
    $expiresAt = (new DateTime())->modify("+$expiresInMinutes minute")->format('Y-m-d H:i:s');

    return ['token' => $token, 'expiresAt' => $expiresAt];
}
