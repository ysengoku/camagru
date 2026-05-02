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

    <?php if (empty($posts)): ?>
      <p>No posts yet.</p>
    <?php else: ?>
      <?php foreach ($posts as $post): ?>
        <article class="post" id="post-<?php echo htmlspecialchars($post['id'], ENT_QUOTES); ?>">
          <header>
            <strong><?php echo htmlspecialchars($post['author_name'], ENT_QUOTES); ?></strong>
            <span class="meta"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($post['created_at'])), ENT_QUOTES); ?></span>
          </header>

          <?php if (!empty($post['image_path'])): ?>
            <div class="post-image">
              <img src="<?php echo htmlspecialchars($post['image_path'], ENT_QUOTES); ?>" alt="Post image" style="max-width:100%;height:auto;">
            </div>
          <?php endif; ?>

          <?php if (!empty($post['caption'])): ?>
            <p class="caption"><?php echo nl2br(htmlspecialchars($post['caption'], ENT_QUOTES)); ?></p>
          <?php endif; ?>

          <div class="post-actions">
            <form method="post" action="/post/<?php echo urlencode($post['id']); ?>/like">
              <button type="submit">Like (<?php echo (int)$post['likes_count']; ?>)</button>
            </form>
          </div>

          <section class="comments">
            <h4>Comments</h4>
            <?php if (!empty($post['comments'])): ?>
              <ul>
                <?php foreach ($post['comments'] as $c): ?>
                  <li>
                    <strong><?php echo htmlspecialchars($c['author_name'] ?? ''); ?>:</strong>
                    <?php echo nl2br(htmlspecialchars($c['text'] ?? '', ENT_QUOTES)); ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p>No comments.</p>
            <?php endif; ?>

            <?php if ($user): ?>
              <form method="post" action="/post/<?php echo urlencode($post['id']); ?>/comment">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id'], ENT_QUOTES); ?>">
                <textarea name="comment" rows="2" required></textarea>
                <button type="submit">Add comment</button>
              </form>
            <?php else: ?>
              <p><a href="/login">Log in</a> to comment.</p>
            <?php endif; ?>
          </section>
        </article>
        <hr>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</body>
</html>
