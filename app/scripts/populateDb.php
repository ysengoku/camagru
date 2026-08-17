#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/core/Database.php';
require_once __DIR__ . '/../src/core/Model.php';
require_once __DIR__ . '/../src/helper/Path.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Post.php';
require_once __DIR__ . '/../src/Models/Like.php';
require_once __DIR__ . '/../src/Models/Comment.php';
require_once __DIR__ . '/../src/Services/ImageComposer.php';

const TEST_PASSWORD = '123';
const FIXTURES_DIR = __DIR__ . '/fixtures';
const FIXTURE_IMAGES = ['sample-pic.jpg', 'sample-pic2.jpg', 'sample-pic3.jpg'];

$fixtures = require __DIR__ . '/fixtures/demoData.php';
define('USER_DATA', $fixtures['users']);
define('COMMENT_POOL', $fixtures['comments']);
define('POST_COMPOSITIONS', $fixtures['posts']);

/** @var list<User> $users */
$demoUsers = [];
/** @var list<Post> $posts */
$demoPosts = [];

function seedUsers(): array {
    $passwordHash = password_hash(TEST_PASSWORD, PASSWORD_DEFAULT);
    $users = [];

    foreach (USER_DATA as $data) {
        $existing = User::findByEmail($data['email']);
        if ($existing !== null) {
            echo "Skipping {$data['email']} (already exists)\n";
            $users[] = $existing;
            continue;
        }

        $user = new User(
            username:$data['username'],
            email: $data['email'],
            passwordHash: $passwordHash,
            emailVerified: 1,
        );

        if (!$user->createNewUser()) {
            echo "Failed to create {$data['username']}\n";
            continue;
        }

        $createdUser = User::findByEmail($data['email']);
        $createdUser->email_notifications_enabled = 0;
        $createdUser->save();

        echo "Created {$data['username']} <{$data['email']}> (password: " . TEST_PASSWORD . ")\n";

        $users[] = $user;
    }

    return $users;
}

/**
 * Composes each configured demo post server-side from its base image, stickers,
 * text overlay, and filter.
 * @return list<Post> $posts
 */
function seedPosts(): array {
    $mediaDir = Path::getMediaDirPath();
    Path::ensureDirectory($mediaDir);
    $posts = [];

    foreach (POST_COMPOSITIONS as $i => $composition) {
        $author = User::findByUsername($composition['author']);
        if ($author === null) {
            echo "Skipping post #{$i}: author '{$composition['author']}' not found\n";
            continue;
        }

        $baseImagePath = FIXTURES_DIR . '/' . ltrim($composition['baseImage'], './');
        $seededFilename = 'seed-' . uniqid('', true) . '.jpg';
        $imagePath = Path::join($mediaDir, $seededFilename);
        $publicPath = '/media/' . $seededFilename;

        try {
            $imageComposer = new ImageComposer($baseImagePath);
            $baseImageSize = getimagesize($baseImagePath);
            [$imgWidth, $imgHeight] = $baseImageSize;

            /**
             * @var list<array{path: string, width: float, height: float, x: float, y: float}> $stickers
             * @var array{content: string, fontFamily: string, fontSize: float, color: string, x: float, y: float}|null $textOverlay
             */
            $stickers = array_map(fn($s) => [
                'path' => $s['path'],
                'x' => $s['xFraction'] * $imgWidth,
                'y' => $s['yFraction'] * $imgHeight,
                'width' => $s['widthFraction'] * $imgWidth,
                'height' => $s['heightFraction'] * $imgHeight,
            ], $composition['stickers']);

            $textOverlay = null;
            if ($composition['textOverlay'] !== null) {
                $t = $composition['textOverlay'];
                $textOverlay = [
                    'content' => $t['content'],
                    'fontFamily' => $t['fontFamily'],
                    'color' => $t['color'],
                    'x' => $t['xFraction'] * $imgWidth,
                    'y' => $t['yFraction'] * $imgHeight,
                    'fontSize' => $t['fontSizeFraction'] * $imgHeight,
                ];
            }

            $saved = $imageComposer->compose($stickers, $textOverlay, $composition['filter'], $imagePath);
        } catch (\Throwable $e) {
            echo "Failed to compose post #{$i} for {$author->username}: " . $e->getMessage() . "\n";
            continue;
        }

        if (!$saved) {
            echo "Failed to save composed image for post #{$i}\n";
            continue;
        }

        $post = new Post($publicPath, $author->id);
        sleep(random_int(1, 3)); // Sleep 1-3 seconds to simulate more realistic timing
        if ($post->save()) {
            $posts[] = $post;
            echo "Created post #{$i} for {$author->username}\n";
        } else {
            echo "Failed to save post for {$author->username}: " . implode(', ', $post->getErrors()) . "\n";
        }
    }

    return $posts;
}


/**
 * Randomly like and comment on a subset of the given posts, using the given verified
 * users as the pool of possible likers/commenters (excluding a post's own author).
 * @param list<Post> $posts
 * @param list<User> $users
 */
function seedEngagement(array $posts, array $users): void {
    $likeCount = 0;
    $commentCount = 0;

    foreach ($posts as $post) {
        $possibleLikers = array_values(array_filter(
            $users,
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
            $commenter = $users[array_rand($users)];
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

/**
 * Tops up the given post's comment count to exactly $targetCount, so there's a
 * predictable post to exercise the "load more comments" pagination against.
 * @param list<User> $users
 */
function seedCommentsForPagination(Post $post, array $users, int $targetCount): void {
    $existingCount = Comment::countByPostId($post->id);
    $toAdd = $targetCount - $existingCount;
    if ($toAdd <= 0) {
        return;
    }

    $possibleCommenters = array_values(array_filter(
        $users,
        fn(User $user): bool => $user->id !== $post->user_id
    ));
    if (empty($possibleCommenters)) {
        return;
    }

    for ($i = 0; $i < $toAdd; $i++) {
        $commenter = $possibleCommenters[array_rand($possibleCommenters)];
        $comment = new Comment();
        $comment->post_id = $post->id;
        $comment->author_id = $commenter->id;
        $comment->content = COMMENT_POOL[array_rand(COMMENT_POOL)];
        $comment->save();
    }

    echo "Post #{$post->id} now has " . Comment::countByPostId($post->id) . " comment(s) (pagination test)\n";
}

$demoUsers = seedUsers();
$demoPosts = seedPosts();
seedEngagement($demoPosts, $demoUsers);
seedCommentsForPagination($demoPosts[rand(0, count($demoPosts) - 1)], $demoUsers, 12);
