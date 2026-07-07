#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/core/Database.php';
require_once __DIR__ . '/../src/core/Model.php';
require_once __DIR__ . '/../src/helper/Path.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Post.php';

const TEST_PASSWORD = '123';
const FIXTURES_DIR = __DIR__ . '/fixtures';
const FIXTURE_IMAGES = ['sample-pic.jpg', 'sample-pic2.jpg', 'sample-pic3.jpg'];

/**
 * Copy the fixture images into real media storage and create a Post per image,
 * so demo data goes through the same /media/... path a real upload would use.
 * @return list<Post>
 */
function seedPosts(User $user): array {
    $mediaDir = Path::getMediaDirPath();
    Path::ensureDirectory($mediaDir);

    $posts = [];
    foreach (FIXTURE_IMAGES as $i => $filename) {
        $seededFilename = "seed-{$user->username}-{$i}.jpg";
        $publicPath = '/media/' . $seededFilename;

        copy(FIXTURES_DIR . '/' . $filename, Path::join($mediaDir, $seededFilename));

        $post = new Post($publicPath, $user->id);
        if ($post->save()) {
            $posts[] = $post;
        } else {
            echo "Failed to create post for {$user->username}: " . implode(', ', $post->getErrors()) . "\n";
        }
    }

    return $posts;
}

$passwordHash = password_hash(TEST_PASSWORD, PASSWORD_DEFAULT);

$users = [
    [
        'username' => 'verified',
        'email' => 'verified_user@test.local',
        'email_verified' => 1,
        'pending_email' => null,
        'email_verification_token' => null,
        'email_verification_token_expires_at' => null,
        'withPosts' => true,
    ],
    [
        'username' => 'unverified',
        'email' => 'unverified_user@test.local',
        'email_verified' => 0,
        'pending_email' => null,
        'email_verification_token' => bin2hex(random_bytes(32)),
        'email_verification_token_expires_at' => (new DateTime('+1 hour'))->format('Y-m-d H:i:s'),
        'withPosts' => false,
    ],
    [
        'username' => 'expired_token',
        'email' => 'expired_token_user@test.local',
        'email_verified' => 0,
        'pending_email' => null,
        'email_verification_token' => bin2hex(random_bytes(32)),
        'email_verification_token_expires_at' => (new DateTime('-1 hour'))->format('Y-m-d H:i:s'),
        'withPosts' => false,
    ],
];

foreach ($users as $data) {
    $existing = User::findByEmail($data['email']);
    if ($existing !== null) {
        echo "Skipping {$data['email']} (already exists)\n";
        continue;
    }

    $user = new User(
        username:$data['username'],
        email: $data['email'],
        passwordHash: $passwordHash,
        emailVerificationToken: $data['email_verification_token'] ?? '',
        emailVerificationTokenExpiresAt: $data['email_verification_token_expires_at'],
        emailVerified: $data['email_verified']
    );

    if (!$user->createNewUser()) {
        echo "Failed to create {$data['username']}\n";
        continue;
    }

    echo "Created {$data['username']} <{$data['email']}> (password: " . TEST_PASSWORD . ")\n";

    if ($data['withPosts']) {
        $posts = seedPosts($user);
        echo "Created " . count($posts) . " post(s) for {$data['username']}\n";

        if (!empty($posts)) {
            $user->avatar = $posts[0]->image_path;
            $user->save()
                ? print("Set avatar for {$data['username']} to {$user->avatar}\n")
                : print("Failed to set avatar for {$data['username']}\n");
        }
    }
}
