<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>
            <?php echo $title ?? 'Camagru'; ?>
        </title>
        <link rel="stylesheet" href="/styles/main.css">
    </head>
    <body>
        <?php echo $header ?? ''; ?>

        <main class="container">
            <?php echo $content ?? ''; ?>
        </main>

        <?php echo $footer ?? ''; ?>
    </body>
</html>
