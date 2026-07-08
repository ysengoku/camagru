#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/core/Database.php';
require_once __DIR__ . '/../src/core/Model.php';
require_once __DIR__ . '/../src/helper/Path.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Post.php';
require_once __DIR__ . '/../src/Models/Like.php';
require_once __DIR__ . '/../src/Models/Comment.php';

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

const COMMENT_POOL = [
    'Love this!',
    'Amazing shot!',
    'Wow, stunning!',
    'This is beautiful.',
    'Great capture!',
    'So cool!',
    'Incredible!',
    'Nice one!',
    'This made my day.',
    'Absolutely gorgeous.',
];

/**
 * Randomly like and comment on a subset of the given posts, using the given verified
 * users as the pool of possible likers/commenters (excluding a post's own author).
 * @param list<Post> $posts
 * @param list<User> $verifiedUsers
 */
function seedEngagement(array $posts, array $verifiedUsers): void {
    $likeCount = 0;
    $commentCount = 0;

    foreach ($posts as $post) {
        $possibleLikers = array_values(array_filter(
            $verifiedUsers,
            fn(User $user): bool => $user->id !== $post->user_id
        ));
        shuffle($possibleLikers);

        $likersForPost = array_slice($possibleLikers, 0, random_int(0, min(count($possibleLikers), 4)));
        foreach ($likersForPost as $liker) {
            $like = new Like();
            $like->post_id = $post->id;
            $like->author_id = $liker->id;
            if ($like->save()) {
                $likeCount++;
            }
        }

        $numComments = random_int(0, 3);
        for ($i = 0; $i < $numComments; $i++) {
            $commenter = $verifiedUsers[array_rand($verifiedUsers)];
            $comment = new Comment();
            $comment->post_id = $post->id;
            $comment->author_id = $commenter->id;
            $comment->content = COMMENT_POOL[array_rand(COMMENT_POOL)];
            if ($comment->save()) {
                $commentCount++;
            }
        }
    }

    echo "Created {$likeCount} like(s) and {$commentCount} comment(s) across " . count($posts) . " post(s)\n";
}

$passwordHash = password_hash(TEST_PASSWORD, PASSWORD_DEFAULT);

$users = [
    [
        'username' => 'Yuko',
        'email' => 'yuko@test.local',
        'email_verified' => 1,
        'pending_email' => null,
        'email_verification_token' => null,
        'email_verification_token_expires_at' => null,
        'withPosts' => true,
    ],
    [
        'username' => 'martine',
        'email' => 'martine@test.local',
        'email_verified' => 1,
        'pending_email' => null,
        'email_verification_token' => null,
        'email_verification_token_expires_at' => null,
        'withPosts' => true,
    ],
    [
        'username' => 'claude',
        'email' => 'claude@test.local',
        'email_verified' => 1,
        'pending_email' => null,
        'email_verification_token' => null,
        'email_verification_token_expires_at' => null,
        'withPosts' => true,
    ],
    [
        'username' => 'David',
        'email' => 'david@test.local',
        'email_verified' => 1,
        'pending_email' => null,
        'email_verification_token' => null,
        'email_verification_token_expires_at' => null,
        'withPosts' => false,
    ],
    [
        'username' => 'guy',
        'email' => 'guy@test.local',
        'email_verified' => 1,
        'pending_email' => null,
        'email_verification_token' => null,
        'email_verification_token_expires_at' => null,
        'withPosts' => false,
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

/** @var list<User> $newVerifiedUsers */
$newVerifiedUsers = [];
/** @var list<Post> $newPosts */
$newPosts = [];

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

    if ($data['email_verified'] === 1) {
        $newVerifiedUsers[] = $user;
    }

    if ($data['withPosts']) {
        $posts = seedPosts($user);
        echo "Created " . count($posts) . " post(s) for {$data['username']}\n";
        $newPosts = array_merge($newPosts, $posts);

        if (!empty($posts)) {
            $user->avatar = $posts[0]->image_path;
            $user->save()
                ? print("Set avatar for {$data['username']} to {$user->avatar}\n")
                : print("Failed to set avatar for {$data['username']}\n");
        }
    }
}

if (!empty($newPosts) && count($newVerifiedUsers) > 1) {
    seedEngagement($newPosts, $newVerifiedUsers);
}
