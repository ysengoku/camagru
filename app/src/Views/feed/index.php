<?php
// Expected variables:
// - $user: current user array or null
// - $posts: array of posts, each post is an associative array with keys:
//   id, author_name, author_id, image_path, caption, created_at, likes_count, comments (array)
?>


<?php if (empty($posts)) : ?>
  <p>No posts yet.</p>
<?php else : ?>
    <?php foreach ($posts as $post) : ?>
    <hr>
    <?php endforeach; ?>
<?php endif; ?>

