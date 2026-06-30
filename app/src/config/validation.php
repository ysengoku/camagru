<?php

return [
    'username' => [
        'minLength' => 3,
        'maxLength' => 20,
        'pattern'   => '^[a-zA-Z0-9_]+$',
        'messages'  => [
            'required'  => 'Username is required.',
            'pattern'   => 'Username must contain only letters, numbers, and underscores.',
        ]
    ],

    'password' => [
        'minLength' => 14,
        'maxLength' => 72,
        'requireLower'   => true,
        'requireUpper'   => true,
        'requireDigit'   => true,
        'specialCharPattern' => '[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]',
        'messages'  => [
            'required'  => 'Password is required.',
            'match'     => 'Password and password confirmation do not match.',
            'pattern' => 'Password must contain at least one lowercase, uppercase, digit, and special character.',
        ]
    ],
    'email' => [
        'maxLength' => 254,
        'pattern' => '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$',
        'messages'  => [
            'required'  => 'Email is required.',
            'pattern'   => 'Please enter a valid email address.',
        ]
    ]
];
