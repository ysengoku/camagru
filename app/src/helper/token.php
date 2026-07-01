<?php

function generateToken(int $length = 32): array {
    $token = bin2hex(random_bytes($length));
    $expiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

    return ['token' => $token, 'expiresAt' => $expiresAt];
}
