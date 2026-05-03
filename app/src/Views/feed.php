<?php
// Expected variables:
// - $user: current user array or null
// - $posts: array of posts, each post is an associative array with keys:
//   id, author_name, author_id, image_path, caption, created_at, likes_count, comments (array)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Feed</title>
  <link rel="stylesheet" href="/assets/css/output.css">
</head>
<body>
  <main class="container">
    <h1>Feed</h1>

    <?php if (empty($posts)) : ?>
      <p>No posts yet.</p>
    <?php else : ?>
        <?php foreach ($posts as $post) : ?>
        <hr>
        <?php endforeach; ?>
    <?php endif; ?>
  </main>
</body>
</html>
