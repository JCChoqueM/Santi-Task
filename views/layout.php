<?php
$isDev = esDesarrollo();
$cssProd = '';
if (!$isDev) {
    $cssProd = manifestVite()['src/scss/app.scss']['file'] ?? '';
    if ($cssProd === '') {
        error_log("Vite: falta public/build/.vite/manifest.json o la entrada src/scss/app.scss (falta npm run prod?)");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClaudeTask | <?= $titulo ?? '' ; ?> </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if ($isDev): ?>
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <link rel="stylesheet" href="http://localhost:5173/src/scss/app.scss">
    <?php elseif ($cssProd !== ''): ?>
        <link rel="stylesheet" href="/build/<?= s($cssProd) ?>">
    <?php endif ?>
</head>
<body>
    <?= $contenido ?>
    <?= $script ?? '' ?>
</body>
</html>