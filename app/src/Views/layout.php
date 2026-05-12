<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>
            <?php echo $title ?? 'Camagru'; ?>
        </title>
        <link rel="stylesheet" href="/styles/main.css">
        <link rel="stylesheet" href="/styles/utilities.css">
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
    </head>
    <body>
        <?php echo $header ?? ''; ?>

        <main class="container mx-auto">
            <?php echo $content ?? ''; ?>
        </main>

        <?php echo $footer ?? ''; ?>
    <script type="module" src="/assets/js/main.js"></script>
    </body>
</html>
