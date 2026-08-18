<?php

$usernameMin = 3;
$usernameMax = 15;
$emailMax = 254;
$passwordMin = 14;
$passwordMax = 72;

return [
    'username' => [
        'minLength' => $usernameMin,
        'maxLength' => $usernameMax,
        'pattern'   => '^[a-zA-Z0-9_]+$',
        'messages'  => [
            'required' => 'Username is required.',
            'length'   => "Username must be between {$usernameMin} and {$usernameMax} characters long.",
            'pattern'  => 'Username must contain only letters, numbers, and underscores.',
        ]
    ],

    'email' => [
        'maxLength' => $emailMax,
        'pattern'   => '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$',
        'messages'  => [
            'required' => 'Email is required.',
            'pattern'  => 'Please enter a valid email address.',
            'length'   => "Email must not exceed {$emailMax} characters."
        ]
    ],

    'password' => [
        'minLength' => $passwordMin,
        'maxLength' => $passwordMax,
        'requireLower'   => true,
        'requireUpper'   => true,
        'requireDigit'   => true,
        'specialCharPattern' => '[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]',
        'messages'  => [
            'required' => 'Password is required.',
            'match'    => 'Password and password confirmation do not match.',
            'pattern'  => 'Password must contain at least one lowercase, uppercase, digit, and special character.',
            'length'   => "Password must be between {$passwordMin} and {$passwordMax} characters long.",
        ]
    ]
];
