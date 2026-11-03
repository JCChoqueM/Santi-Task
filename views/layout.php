<?php
$isDev = $_ENV['ENVIRONMENT'] === 'development';
$manifest = null;
if (!$isDev) {
    $manifestPath = __DIR__ . '/../public/build/.vite/manifest.json';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
    } else {
        // Si no existe manifest, fallback a dev
        $isDev = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClaudeTask | <?= $titulo ?? '' ; ?> </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Open+Sans&display=swap" rel="stylesheet">
    <?php if ($isDev): ?>
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <link rel="stylesheet" href="http://localhost:5173/src/scss/app.scss">
    <?php else: ?>
        <link rel="stylesheet" href="/build/<?= $manifest['src/scss/app.scss']['file'] ?? '' ?>">
    <?php endif ?>
</head>
<body>
    <?= $contenido ?>
    <?= $script ?? '' ?>
</body>
</html>