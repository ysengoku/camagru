<?php

return [
    'username' => [
        'minLength' => 3,
        'maxLength' => 20,
        'pattern' => '^[a-zA-Z0-9_]+$'
    ],
    'password' => [
        'minLength' => 14,
        'maxLength' => 72,
        'requireLower'   => true,
        'requireUpper'   => true,
        'requireDigit'   => true,
        'specialCharPattern' => '[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]',
    ],
    'email' => [
        'maxLength' => 254,
        'pattern' => '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$'
    ]
];
