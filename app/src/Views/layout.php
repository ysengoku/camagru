<?php
/**
 * @var string $pageTitle
 * @var string $header
 * @var string $content
 * @var string $footer
 * @var string|null $pageScript
 */
?>

<!doctype html>
<?php $isProd = getenv('NODE_ENV') === 'production'; ?>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>
            <?php echo htmlspecialchars($pageTitle); ?>
        </title>
        <?php if ($isProd): ?>
            <link rel="stylesheet" href="/assets/main.css">
        <?php endif; ?>
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
    </head>
    <body>
        <?php echo $header; ?>

        <main class="container mx-auto">
            <?php echo $content; ?>
        </main>

        <?php echo $footer; ?>
        <?php if ($isProd): ?>
            <script type="module" src="/assets/main.js"></script>
            <?php if ($pageScript !== null): ?>
                <script type="module" src="/assets/<?php echo htmlspecialchars($pageScript); ?>.js"></script>
            <?php endif; ?>
        <?php else: ?>
            <script type="module" src="/@vite/client"></script>
            <script type="module" src="/js/main.js"></script>
            <?php if ($pageScript !== null): ?>
                <script type="module" src="/js/<?php echo htmlspecialchars($pageScript); ?>/entry.js"></script>
            <?php endif; ?>
        <?php endif; ?>
    </body>
</html>
